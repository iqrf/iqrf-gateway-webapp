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

use App\CloudModule\Model\InteliGlueManager;
use App\CloudModule\Presenters\InteliGluePresenter;
use App\CoreModule\Forms\FormFactory;
use App\ServiceModule\Model\ServiceManager;
use Nette;
use Nette\Forms\Form;
use Nette\Forms\Controls\SubmitButton;

/**
 * Form for creating MQTT connection into Inteliments InteliGlue
 */
class InteliGlueFormFactory extends CloudFormFactory {

	use Nette\SmartObject;

	/**
	 * Constructor
	 * @param InteliGlueManager $manager Inteliments InteliGlue manager
	 * @param FormFactory $factory Generic form factory
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(InteliGlueManager $manager, FormFactory $factory, ServiceManager $serviceManager) {
		parent::__construct($manager, $factory, $serviceManager);
	}

	/**
	 * Create MQTT configuration form
	 * @param InteliGluePresenter $presenter Inteliments InteliGlue presenter
	 * @return Form MQTT configuration form
	 */
	public function create(InteliGluePresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('cloud.intelimentsInteliGlue.form'));
		$form->addText('rootTopic', 'rootTopic')->setRequired();
		$form->addInteger('assignedPort', 'assignedPort')->setRequired()
				->addRule(Form::RANGE, 'Port have to be in range from 0 to 65535', [0, 65535]);
		$form->addText('clientId', 'clientId')->setRequired();
		$form->addText('password', 'password')->setRequired();
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

}
