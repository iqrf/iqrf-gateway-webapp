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

namespace App\ConfigModule\Forms;

use App\ConfigModule\Presenters\IqrfInfoPresenter;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * IQRF Info configuration form factory
 */
class IqrfInfoFormFactory extends GenericConfigFormFactory {

	use SmartObject;

	/**
	 * Creates the IQRF Info configuration form
	 * @param IqrfInfoPresenter $presenter IQRF Info configuration presenter
	 * @return Form IQRF Info configuration form
	 * @throws JsonException
	 */
	public function create(IqrfInfoPresenter $presenter): Form {
		$this->manager->setComponent('iqrf::IqrfInfo');
		$this->presenter = $presenter;
		$form = $this->factory->create('config.iqrfInfo.form');
		$form->addText('instance', 'instance')
			->setRequired('messages.instance');
		$form->addCheckbox('enumAtStartUp', 'enumAtStartUp');
		$form->addInteger('enumPeriod', 'enumPeriod')
			->addRule(Form::MIN, 'messages.enumPeriod', 0);
		$form->addCheckbox('enumUniformDpaVer', 'enumUniformDpaVer');
		$form->addSubmit('save', 'save');
		$form->addProtection('core.errors.form-timeout');
		$form->setDefaults($this->manager->load(0));
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

}
