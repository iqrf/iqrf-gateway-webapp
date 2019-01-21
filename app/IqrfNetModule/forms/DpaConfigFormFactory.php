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

namespace App\IqrfNetModule\Forms;

use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Presenters\DpaConfigPresenter;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * IQRF TR - DPA configuration form factory
 */
class DpaConfigFormFactory extends TrConfigFormFactory {

	use SmartObject;

	/**
	 * Create DPA configuration form
	 * @param DpaConfigPresenter $presenter DPA configuration presenter
	 * @return Form DPA configuration form
	 */
	public function create(DpaConfigPresenter $presenter): Form {
		$this->presenter = $presenter;
		try {
			$this->configuration = $this->load();
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->configuration = [];
		}
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('iqrfnet.dpaConfig'));
		$this->addEmbeddedPeripherals($form);
		$this->addRfConfiguration($form);
		$this->addOtherConfiguration($form);
		$form->addSubmit('save', 'save');
		$form->addProtection('core.errors.form-timeout');
		$form->setDefaults($this->configuration);
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Add embedded peripherals to the form
	 * @param Form $form DPA configuration form
	 */
	private function addEmbeddedPeripherals(Form &$form): void {
		$peripherals = ['coordinator', 'node', 'os', 'eeprom', 'eeeprom', 'ram','ledr',	'ledg', 'spi', 'io', 'thermometer', 'pwm', 'uart', 'frc'];
		$embPers = $form->addContainer('embPers');
		foreach ($peripherals as $peripheral) {
			$embPers->addCheckbox($peripheral, 'embPers.' . $peripheral);
		}
	}

	/**
	 * Add RF configuration to the form
	 * @param Form $form DPA configuration form
	 */
	private function addRfConfiguration(Form &$form): void {
		if (array_key_exists('stdAndLpNetwork', $this->configuration)) {
			$networkTypes = [false => 'networkTypes.std', true => 'networkTypes.stdLp'];
			$form->addSelect('networkType', 'networkType', $networkTypes);
		}
		$form->addInteger('txPower', 'txPower')->addRule(Form::RANGE, 'messages.txPower', [0, 7])
			->setRequired('messages.txPower');
		$form->addInteger('rxFilter', 'rxFilter')->addRule(Form::RANGE, 'messages.rxFilter', [0, 64])
			->setRequired('messages.rxFilter');
		$form->addInteger('lpRxTimeout', 'lpRxTimeout')->addRule(Form::RANGE, 'messages.lpRxTimeout', [1, 255])
			->setRequired('messages.lpRxTimeout');
		$subChannels = ['rfSubChannelA', 'rfSubChannelB'];
		foreach ($subChannels as $subChannel) {
			if (array_key_exists($subChannel, $this->configuration)) {
				$form->addInteger($subChannel, $subChannel);
				$this->setRfChannelRule($form[$subChannel]);
			}
		}
	}

	/**
	 * Add other configuration to the form
	 * @param Form $form DPA configuration form
	 */
	private function addOtherConfiguration(Form &$form): void {
		$form->addCheckbox('customDpaHandler', 'customDpaHandler');
		$form->addCheckbox('ioSetup', 'ioSetup');
		$form->addCheckbox('dpaAutoexec', 'dpaAutoexec');
		$form->addCheckbox('routingOff', 'routingOff');
		$form->addCheckbox('peerToPeer', 'peerToPeer');
		if (array_key_exists('neverSleep', $this->configuration)) {
			$form->addCheckbox('neverSleep', 'neverSleep');
		}
		$uartBaudrates = [1200, 2400, 4800, 9600, 19200, 38400, 57600, 115200, 230400];
		foreach ($uartBaudrates as $key => $baudrate) {
			$uartBaudrates[$baudrate] = 'uartBaudrates.' . $baudrate;
			unset($uartBaudrates[$key]);
		}
		$form->addSelect('uartBaudrate', 'uartBaudrate', $uartBaudrates);
	}

}
