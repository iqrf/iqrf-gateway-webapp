<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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
declare(strict_types = 1);

namespace App\CloudModule\Forms;

use App\CloudModule\Model\AzureManager;
use App\CloudModule\Presenters\AzurePresenter;
use App\CloudModule\Model\InvalidConnectionString;
use App\Forms\FormFactory;
use App\Model\NonExistingJsonSchemaException;
use App\ServiceModule\Model\NotSupportedInitSystemException;
use App\ServiceModule\Model\ServiceManager;
use Nette;
use Nette\Forms\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\IOException;

/**
 * Form for creating MQTT connection into Microsoft Azure IoT Hub
 */
class CloudAzureMqttFormFactory {

	use Nette\SmartObject;

	/**
	 * @var AzureManager Microsoft Azure IoT Hub manager
	 */
	private $cloudManager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var AzurePresenter MS Azure IoT presenter
	 */
	private $presenter;

	/**
	 * @var ServiceManager Service manager
	 */
	private $serviceManager;

	/**
	 * Constructor
	 * @param AzureManager $azure Microsoft Azure IoT Hub manager
	 * @param FormFactory $factory Generic form factory
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(AzureManager $azure, FormFactory $factory, ServiceManager $serviceManager) {
		$this->cloudManager = $azure;
		$this->factory = $factory;
		$this->serviceManager = $serviceManager;
	}

	/**
	 * Create MQTT configuration form
	 * @param AzurePresenter $presenter MS Azure presenter
	 * @return Form MQTT configuration form
	 */
	public function create(AzurePresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('cloud.msAzure.form'));
		$form->addText('ConnectionString', 'connectionString')->setRequired();
		$form->addSubmit('save', 'save')
				->onClick[] = function (SubmitButton $button) {
			$this->save($button);
		};
		$form->addSubmit('save_restart', 'save_restart')
				->onClick[] = function (SubmitButton $button) {
			$this->save($button, true);
		};
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

	/**
	 * Create the MQTT interface
	 * @param SubmitButton $button Form's sumbit button
	 * @param bool $needRestart Is restart needed?
	 */
	public function save(SubmitButton $button, bool $needRestart = false) {
		$values = $button->getForm()->getValues();
		try {
			$this->cloudManager->createMqttInterface($values['ConnectionString']);
			$this->presenter->flashMessage('cloud.messages.success', 'success');
			$this->presenter->redirect(':Config:Mqtt:default');
		} catch (\Exception $e) {
			if ($e instanceof InvalidConnectionString) {
				$this->presenter->flashMessage('cloud.msAzure.messages.invalidConnectionString', 'danger');
			} else if ($e instanceof NonExistingJsonSchemaException) {
				$this->presenter->flashMessage('config.messages.nonExistingJsonSchema', 'danger');
			} else if ($e instanceof IOException) {
				$this->presenter->flashMessage('config.messages.writeFailure', 'danger');
			} else {
				throw $e;
			}
		}
		if ($needRestart) {
			try {
				$this->serviceManager->restart();
				$this->presenter->flashMessage('service.actions.restart.message', 'info');
			} catch (NotSupportedInitSystemException $e) {
				$this->presenter->flashMessage('service.errors.unsupportedInit', 'danger');
			}
		}
	}

}
