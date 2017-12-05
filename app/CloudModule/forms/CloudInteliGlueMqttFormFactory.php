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

use App\CloudModule\Model\InteliGlueManager;
use App\CloudModule\Presenters\InteliGluePresenter;
use App\ConfigModule\Model\BaseServiceManager;
use App\ConfigModule\Model\InstanceManager;
use App\Forms\FormFactory;
use Nette;
use Nette\Application\UI\Form;
use Nette\IOException;

class CloudInteliGlueMqttFormFactory {

	use Nette\SmartObject;

	/**
	 * @var InteliGlueManager
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
	 * @param BaseServiceManager $baseService
	 * @param InteliGlueManager $inteliGlue
	 * @param InstanceManager $manager
	 * @param FormFactory $factory
	 */
	public function __construct(BaseServiceManager $baseService, InteliGlueManager $inteliGlue, InstanceManager $manager, FormFactory $factory) {
		$this->baseService = $baseService;
		$this->cloudManager = $inteliGlue;
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create MQTT configuration form
	 * @param InteliGluePresenter $presenter
	 * @return Form MQTT configuration form
	 */
	public function create(InteliGluePresenter $presenter) {
		$form = $this->factory->create();
		$fileName = 'MqttMessaging';
		$this->manager->setFileName($fileName);
		$form->addText('rootTopic', 'Root Topic')->setRequired();
		$form->addInteger('assignedPort', 'AssignedPort')->setRequired()->setDefaultValue(1883)
				->addRule(Form::RANGE, 'Port have to be in range from 0 to 65535', [0, 65535]);
		$form->addText('clientId', 'ClientId')->setRequired();
		$form->addText('password', 'Password')->setRequired();
		$form->addSubmit('save', 'Save');
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			try {
				$settings = $this->cloudManager->createMqttInterface($values);
				$baseService = $this->cloudManager->createBaseService();
				$this->baseService->add($baseService);
				$this->manager->add($settings);
				$presenter->redirect(':Config:Mqtt:default');
			} catch (IOException $e) {
				$presenter->flashMessage('IQRF Daemon\'s configuration files not found.', 'danger');
			}
		};
		return $form;
	}

}
