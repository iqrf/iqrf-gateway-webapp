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

use App\CloudModule\Models\TcPisekManager;
use App\CloudModule\Presenters\TcPisekPresenter;
use App\CoreModule\Forms\FormFactory;
use App\ServiceModule\Models\ServiceManager;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;

/**
 * Form for creating MQTT connection into TC Písek IoT platform
 */
class TcPisekFormFactory extends CloudFormFactory {

	use SmartObject;

	/**
	 * Constructor
	 * @param TcPisekManager $manager TC Písek IoT platform manager
	 * @param FormFactory $factory Generic form factory
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(TcPisekManager $manager, FormFactory $factory, ServiceManager $serviceManager) {
		parent::__construct($manager, $factory, $serviceManager);
	}

	/**
	 * Creates the TC Písek IoT platform form
	 * @param TcPisekPresenter $presenter TC Písek IoT platform presenter
	 * @return Form TC Písek IoT platform form
	 */
	public function create(TcPisekPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('cloud.tcPisek.form');
		$form->addText('broker', 'broker')
			->setRequired('messages.broker')
			->setDefaultValue('connect.iot.tcpisek.cz');
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
