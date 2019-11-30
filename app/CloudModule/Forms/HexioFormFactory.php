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

use App\CloudModule\Models\HexioManager;
use App\CloudModule\Presenters\HexioPresenter;
use App\CoreModule\Forms\FormFactory;
use App\ServiceModule\Models\ServiceManager;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;

/**
 * Form for creating MQTT connection into Hexio IoT platform
 */
class HexioFormFactory extends CloudFormFactory {

	use SmartObject;

	/**
	 * Constructor
	 * @param HexioManager $manager Hexio IoT platform manager
	 * @param FormFactory $factory Generic form factory
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(HexioManager $manager, FormFactory $factory, ServiceManager $serviceManager) {
		parent::__construct($manager, $factory, $serviceManager);
	}

	/**
	 * Creates the Hexio IoT platform form
	 * @param HexioPresenter $presenter Hexio IoT platform presenter
	 * @return Form Hexio IoT platform form
	 */
	public function create(HexioPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('cloud.hexio.form');
		$form->addText('broker', 'broker')
			->setRequired('messages.broker')
			->setDefaultValue('connect.hexio.cloud');
		$form->addText('username', 'username')
			->setRequired('messages.username');
		$form->addText('password', 'password')
			->setRequired('messages.password');
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
