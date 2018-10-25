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

use App\CloudModule\Models\BluemixManager;
use App\CloudModule\Presenters\BluemixPresenter;
use App\CoreModule\Forms\FormFactory;
use App\ServiceModule\Models\ServiceManager;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\SmartObject;

/**
 * Form for creating MQTT connection into IBM Bluemíx
 */
class BluemixFormFactory extends CloudFormFactory {

	use SmartObject;

	/**
	 * Constructor
	 * @param BluemixManager $manager IBM Bluemix manager
	 * @param FormFactory $factory Generic form factory
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(BluemixManager $manager, FormFactory $factory, ServiceManager $serviceManager) {
		parent::__construct($manager, $factory, $serviceManager);
	}

	/**
	 * Create MQTT configuration form
	 * @param BluemixPresenter $presenter IBM Bluemix presenter
	 * @return Form MQTT configuration form
	 */
	public function create(BluemixPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('cloud.ibmBluemix.form'));
		$form->addText('organizationId', 'organizationId')->setRequired();
		$form->addText('deviceType', 'deviceType')->setRequired();
		$form->addText('deviceId', 'deviceId')->setRequired();
		$form->addText('token', 'token')->setRequired();
		$form->addText('eventId', 'eventId')->setRequired()->setDefaultValue('iqrf');
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
