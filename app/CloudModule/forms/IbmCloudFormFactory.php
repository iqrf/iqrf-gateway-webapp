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

use App\CloudModule\Models\IbmCloudManager;
use App\CloudModule\Presenters\IbmCloudPresenter;
use App\CoreModule\Forms\FormFactory;
use App\ServiceModule\Models\ServiceManager;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\SmartObject;

/**
 * Form for creating MQTT connection into IBM Cloud
 */
class IbmCloudFormFactory extends CloudFormFactory {

	use SmartObject;

	/**
	 * Constructor
	 * @param IbmCloudManager $manager IBM Cloud manager
	 * @param FormFactory $factory Generic form factory
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(IbmCloudManager $manager, FormFactory $factory, ServiceManager $serviceManager) {
		parent::__construct($manager, $factory, $serviceManager);
	}

	/**
	 * Creates the IBM Cloud form
	 * @param IbmCloudPresenter $presenter IBM Cloud presenter
	 * @return Form IBM Cloud form
	 */
	public function create(IbmCloudPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('cloud.ibmCloud.form'));
		$form->addText('organizationId', 'organizationId')->setRequired();
		$form->addText('deviceType', 'deviceType')->setRequired();
		$form->addText('deviceId', 'deviceId')->setRequired();
		$form->addText('token', 'token')->setRequired();
		$form->addText('eventId', 'eventId')->setRequired()->setDefaultValue('iqrf');
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
