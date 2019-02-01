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

use App\CloudModule\Models\AwsManager;
use App\CloudModule\Presenters\AwsPresenter;
use App\CoreModule\Forms\FormFactory;
use App\ServiceModule\Models\ServiceManager;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\SmartObject;

/**
 * Form for creating MQTT connection into Amazon AWS IoT
 */
class AwsFormFactory extends CloudFormFactory {

	use SmartObject;

	/**
	 * Constructor
	 * @param AwsManager $manager Amazon AWS IoT manager
	 * @param FormFactory $factory Generic form factory
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(AwsManager $manager, FormFactory $factory, ServiceManager $serviceManager) {
		parent::__construct($manager, $factory, $serviceManager);
	}

	/**
	 * Create MQTT configuration form
	 * @param AwsPresenter $presenter Amazon AWS IoT presenter
	 * @return Form MQTT configuration form
	 */
	public function create(AwsPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('cloud.amazonAws.form'));
		$form->addText('endpoint', 'endpoint')->setRequired();
		$form->addUpload('cert', 'certificate')->setRequired();
		$form->addUpload('key', 'pkey')->setRequired();
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
