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

declare(strict_types=1);

namespace App\CloudModule\Forms;

use App\CloudModule\Model\AwsManager;
use App\CloudModule\Presenters\AwsPresenter;
use App\ConfigModule\Model\BaseServiceManager;
use App\ConfigModule\Model\InstanceManager;
use App\Forms\FormFactory;
use Nette;
use Nette\Application\UI\Form;
use Nette\IOException;
use Nette\Utils\ArrayHash;

/**
 * Form for creating MQTT instance and Base service for Amazon AWS IoT
 */
class CloudAwsMqttFormFactory {

	use Nette\SmartObject;

	/**
	 * @var AwsManager Amazon AWS IoT manager
	 */
	private $cloudManager;

	/**
	 * @var BaseServiceManager Base service manager
	 */
	private $baseService;

	/**
	 * @var InstanceManager MQTT instance manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param AwsManager $aws Amazon AWS IoT manager
	 * @param BaseServiceManager $baseService Base service manager\n
	 * @param InstanceManager $manager MQTT instance manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(AwsManager $aws, BaseServiceManager $baseService, InstanceManager $manager, FormFactory $factory) {
		$this->cloudManager = $aws;
		$this->baseService = $baseService;
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create MQTT configuration form
	 * @param AwsPresenter $presenter Amazon AWS IoT presenter
	 * @return Form MQTT configuration form
	 */
	public function create(AwsPresenter $presenter): Form {
		$form = $this->factory->create();
		$fileName = 'MqttMessaging';
		$this->manager->setFileName($fileName);
		$form->addText('endpoint', 'Endpoint')->setRequired();
		$form->addUpload('caCert', 'Root CA certificate')->setRequired();
		$form->addUpload('cert', 'Certificate')->setRequired();
		$form->addUpload('key', 'Private key')->setRequired();
		$form->addSubmit('save', 'Save');
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			$this->onSuccess($values, $presenter);
		};
		return $form;
	}

	/**
	 * Create the base service and MQTT interface
	 * @param ArrayHash $values Values from the form
	 * @param AwsPresenter $presenter Amazon AWS IoT presenter
	 */
	public function onSuccess(ArrayHash $values, AwsPresenter $presenter) {
		try {
			$mqttInterface = $this->cloudManager->createMqttInterface($values);
			$baseService = $this->cloudManager->createBaseService();
			$this->baseService->add($baseService);
			$this->manager->add($mqttInterface);
			$presenter->redirect(':Config:Mqtt:default');
		} catch (IOException $e) {
			$presenter->flashMessage('IQRF Daemon\'s configuration files not found.', 'danger');
		}
	}

}
