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

namespace App\ConfigModule\Forms;

use App\ConfigModule\Model\AzureManager;
use App\ConfigModule\Model\InstanceManager;
use App\ConfigModule\Presenters\MqttPresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Application\UI\Form;

class ConfigMqttAzureFormFactory {

	use Nette\SmartObject;

	/**
	 * @var AzureManager
	 */
	private $azure;

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
	 * @param InstanceManager $manager
	 * @param FormFactory $factory
	 */
	public function __construct(AzureManager $azure, InstanceManager $manager, FormFactory $factory) {
		$this->azure = $azure;
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create MQTT configuration form
	 * @param MqttPresenter $presenter
	 * @return Form MQTT configuration form
	 */
	public function create(MqttPresenter $presenter) {
		$form = $this->factory->create();
		$fileName = 'MqttMessaging';
		$id = count($this->manager->getInstances($fileName)) + 1;
		$form->addText('ConnectionString', 'ConnectionString')->setRequired();
		$form->addSubmit('save', 'Save');
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter, $fileName, $id) {
			$settings = $this->azure->createMqttInterface($values['ConnectionString']);
			$this->manager->save($fileName, $settings, $id);
			$presenter->redirect('Mqtt:default');
		};
		return $form;
	}

}
