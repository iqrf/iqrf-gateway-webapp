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

use App\CloudModule\Model\AwsManager;
use App\CloudModule\Presenters\AwsPresenter;
use App\ConfigModule\Model\InstanceManager;
use App\Forms\FormFactory;
use Nette;
use Nette\Application\UI\Form;

class CloudAwsMqttFormFactory {

	use Nette\SmartObject;

	/**
	 * @var AwsManager
	 */
	private $aws;

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
	 * @param AwsManager $aws
	 * @param InstanceManager $manager
	 * @param FormFactory $factory
	 */
	public function __construct(AwsManager $aws, InstanceManager $manager, FormFactory $factory) {
		$this->aws = $aws;
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create MQTT configuration form
	 * @param AwsPresenter $presenter
	 * @return Form MQTT configuration form
	 */
	public function create(AwsPresenter $presenter) {
		$form = $this->factory->create();
		$fileName = 'MqttMessaging';
		$this->manager->setFileName($fileName);
		$id = count($this->manager->getInstances());
		$form->addText('endpoint', 'Endpoint')->setRequired();
		$form->addUpload('caCert', 'Root CA certificate')->setRequired();
		$form->addUpload('cert', 'Certificate')->setRequired();
		$form->addUpload('key', 'Private key')->setRequired();
		$form->addSubmit('save', 'Save');
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter, $id) {
			$settings = $this->aws->createMqttInterface($values);
			$this->manager->save($settings, $id);
			$presenter->redirect(':Config:Mqtt:default');
		};
		return $form;
	}

}
