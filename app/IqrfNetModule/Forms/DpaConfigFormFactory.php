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

namespace App\IqrfNetModule\Forms;

use App\IqrfNetModule\Presenters\DpaConfigPresenter;
use Nette\Application\UI\Form;
use Nette\SmartObject;

/**
 * DPA configuration form factory
 */
class DpaConfigFormFactory extends TrConfigFormFactory {

	use SmartObject;

	/**
	 * Creates DPA configuration form
	 * @param DpaConfigPresenter $presenter DPA configuration presenter
	 * @return Form DPA configuration form
	 */
	public function create(DpaConfigPresenter $presenter): Form {
		$this->presenter = $presenter;
		$this->load();
		$form = $this->factory->create('iqrfnet.dpaConfig');
		$this->addEmbeddedPeripherals($form);
		$this->addOtherConfiguration($form);
		$form->addSubmit('save', 'save');
		$form->addProtection('core.errors.form-timeout');
		$form->setDefaults($this->configuration);
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Adds embedded peripherals to the form
	 * @param Form $form DPA configuration form
	 */
	private function addEmbeddedPeripherals(Form &$form): void {
		$form->addGroup('embeddedPeripherals');
		$embeddedPeripherals = $form->addContainer('embPers');
		$unchangeablePeripherals = ['coordinator', 'node', 'os'];
		if ($this->presenter->getUser()->isInRole('power')) {
			foreach ($unchangeablePeripherals as $peripheral) {
				$embeddedPeripherals->addCheckbox($peripheral, 'embPers.' . $peripheral)
					->setDisabled();
				if (array_key_exists('embPers', $this->configuration)) {
					$embeddedPeripherals[$peripheral]->setValue($this->configuration['embPers'][$peripheral]);
				}
			}
		}
		$changeablePeripherals = ['eeprom', 'eeeprom', 'ram', 'ledr', 'ledg', 'spi', 'io', 'thermometer', 'uart', 'frc'];
		foreach ($changeablePeripherals as $peripheral) {
			$embeddedPeripherals->addCheckbox($peripheral, 'embPers.' . $peripheral);
		}
	}

	/**
	 * Add other configuration to the form
	 * @param Form $form DPA configuration form
	 */
	private function addOtherConfiguration(Form &$form): void {
		$form->addGroup('other');
		$form->addCheckbox('customDpaHandler', 'customDpaHandler');
		$form->addCheckbox('ioSetup', 'ioSetup');
		$form->addCheckbox('dpaAutoexec', 'dpaAutoexec');
		$form->addCheckbox('routingOff', 'routingOff');
		$form->addCheckbox('peerToPeer', 'peerToPeer');
		if (array_key_exists('dpaPeerToPeer', $this->configuration)) {
			$form->addCheckbox('dpaPeerToPeer', 'dpaPeerToPeer');
		}
		if (array_key_exists('neverSleep', $this->configuration)) {
			$form->addCheckbox('neverSleep', 'neverSleep');
		}
		$uartBaudrates = [1200, 2400, 4800, 9600, 19200, 38400, 57600, 115200, 230400];
		foreach ($uartBaudrates as $key => $baudrate) {
			$uartBaudrates[$baudrate] = 'uartBaudrates.' . $baudrate;
			unset($uartBaudrates[$key]);
		}
		$form->addSelect('uartBaudrate', 'uartBaudrate', $uartBaudrates);
		if (array_key_exists('nodeDpaInterface', $this->configuration)) {
			$form->addCheckbox('nodeDpaInterface', 'nodeDpaInterface');
		}
	}

}
