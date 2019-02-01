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

use App\ConfigModule\Presenters\IqrfSpiPresenter;
use Nette\Forms\Form;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * IQRF SPI configuration form factory
 */
class IqrfSpiFormFactory extends GenericConfigFormFactory {

	use SmartObject;

	/**
	 * Create IQRF SPI configuration form
	 * @param IqrfSpiPresenter $presenter IQRF SPI configuration presenter
	 * @return Form IQRF SPI interface configuration form
	 * @throws JsonException
	 */
	public function create(IqrfSpiPresenter $presenter): Form {
		$this->manager->setComponent('iqrf::IqrfSpi');
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.iqrfSpi.form'));
		$form->addText('instance', 'instance')->setRequired('messages.instance');
		$form->addText('IqrfInterface', 'IqrfInterface')->setRequired('messages.IqrfInterface');
		$form->addInteger('powerEnableGpioPin', 'powerEnableGpioPin');
		$form->addInteger('busEnableGpioPin', 'busEnableGpioPin');
		$form->addInteger('pgmSwitchGpioPin', 'pgmSwitchGpioPin');
		$form->addCheckbox('spiReset', 'spiReset');
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		$form->setDefaults($this->manager->load(0));
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

}
