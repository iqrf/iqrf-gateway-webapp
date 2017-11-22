<?php

/**
 * Copyright 2017 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\CloudModule\Forms;

use App\CloudModule\Model\AzureManager;
use App\CloudModule\Presenters\AzurePresenter;
use App\ConfigModule\Model\BaseServiceManager;
use App\ConfigModule\Model\InstanceManager;
use App\Forms\FormFactory;
use Nette;
use Nette\Application\UI\Form;

class CloudAzureMqttFormFactory {

	use Nette\SmartObject;

	/**
	 * @var AzureManager
	 */
	private $cloudManager;

	/**
	 * @var BaseServiceManager
	 */
	private $baseService;

	/**
	 * @var InstanceManager
	 */
	private $manager;

	/**
	 * @var FormFactory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param AzureManager $azure
	 * @param BaseServiceManager $baseService
	 * @param InstanceManager $manager
	 * @param FormFactory $factory
	 */
	public function __construct(AzureManager $azure, BaseServiceManager $baseService, InstanceManager $manager, FormFactory $factory) {
		$this->cloudManager = $azure;
		$this->baseService = $baseService;
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create MQTT configuration form
	 * @param AzurePresenter $presenter
	 * @return Form MQTT configuration form
	 */
	public function create(AzurePresenter $presenter) {
		$form = $this->factory->create();
		$fileName = 'MqttMessaging';
		$this->manager->setFileName($fileName);
		$form->addText('ConnectionString', 'ConnectionString')->setRequired();
		$form->addSubmit('save', 'Save');
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			$settings = $this->cloudManager->createMqttInterface($values['ConnectionString']);
			$baseService = $this->cloudManager->createBaseService();
			$this->baseService->add($baseService);
			$this->manager->add($settings);
			$presenter->redirect(':Config:Mqtt:default');
		};
		return $form;
	}

}
