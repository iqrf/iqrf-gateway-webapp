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

namespace App\ConfigModule\Forms;

use App\ConfigModule\Forms\GenericConfigFormFactory;
use App\ConfigModule\Presenters\IqmeshPresenter;
use Nette;
use Nette\Forms\Form;

/**
 * OTA upload configuration form factory
 */
class OtaUploadFormFactory extends GenericConfigFormFactory {

	use Nette\SmartObject;

	/**
	 * Create OTA upload service configuration form
	 * @param IqmeshPresenter $presenter IQMESH services configuration presenter
	 * @return Form OTA upload configuration form
	 */
	public function create(IqmeshPresenter $presenter): Form {
		$this->manager->setComponent('iqrf::OtaUploadService');
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.iqmesh.otaUpload.form'));
		$this->manager->setFileName($this->manager->getInstanceFiles()[0]);
		$form->addText('instance', 'instance')->setRequired('messages.instance');
		$form->addText('uploadPath', 'uploadPath')->setRequired('messages.uploadPath');
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		$form->setDefaults($this->manager->load());
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

}
