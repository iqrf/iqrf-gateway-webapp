<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

use App\CloudModule\Models\AzureManager;
use App\CloudModule\Presenters\AzurePresenter;
use App\CoreModule\Forms\FormFactory;
use App\ServiceModule\Models\ServiceManager;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;

/**
 * Form for creating MQTT connection into Microsoft Azure IoT Hub
 */
class AzureFormFactory extends CloudFormFactory {

	use SmartObject;

	/**
	 * Constructor
	 * @param AzureManager $manager Microsoft Azure IoT Hub manager
	 * @param FormFactory $factory Generic form factory
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(AzureManager $manager, FormFactory $factory, ServiceManager $serviceManager) {
		parent::__construct($manager, $factory, $serviceManager);
	}

	/**
	 * Creates the Microsoft Azure IoT Hub form
	 * @param AzurePresenter $presenter Microsoft Azure IoT Hub presenter
	 * @return Form Microsoft Azure IoT Hub form
	 */
	public function create(AzurePresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('cloud.msAzure.form');
		$form->addText('connectionString', 'connectionString')
			->setRequired('messages.connectionString');
		$form->addSubmit('save', 'save')
			->onClick[] = function (SubmitButton $button): void {
				$this->save($button);
			};
		$form->addSubmit('save_restart', 'save_restart')
			->onClick[] = function (SubmitButton $button): void {
				$this->save($button, true);
			};
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

}
