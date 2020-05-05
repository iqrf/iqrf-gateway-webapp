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

use App\CoreModule\Forms\FormFactory;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Models\TrConfigManager;
use App\IqrfNetModule\Presenters\TrConfigPresenter;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Forms\Controls\TextInput;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use stdClass;

/**
 * IQRF TR configuration form factory
 */
class TrConfigFormFactory {

	use SmartObject;

	/**
	 * @var int Address
	 */
	private $address;

	/**
	 * @var stdClass TR configuration
	 */
	protected $configuration;

	/**
	 * @var Cache Chache
	 */
	private $cache;

	/**
	 * @var TrConfigManager IQRF TR configuration manager
	 */
	protected $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	protected $factory;

	/**
	 * @var TrConfigPresenter IQRF TR configuration presenter presenter
	 */
	protected $presenter;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param TrConfigManager $manager IQRF TR configuration manager
	 * @param IStorage $storage Cache storage
	 */
	public function __construct(FormFactory $factory, TrConfigManager $manager, IStorage $storage) {
		$this->cache = new Cache($storage, 'trConfiguration');
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates IQRF TR configuration form
	 * @param TrConfigPresenter $presenter IQRF TR configuration presenter
	 * @return Form IQRF TR configuration form
	 */
	public function create(TrConfigPresenter $presenter): Form {
		$this->presenter = $presenter;
		$this->address = (int) $presenter->getParameter('address', 0);
		$this->configuration = $this->cache->load('trConfiguration' . $this->address) ?? new stdClass();
		$form = $this->factory->create('iqrfnet.trConfig');
		$this->addRfConfiguration($form);
		$this->addRfpgwConfiguration($form);
		$this->addDpaEmbeddedPeripherals($form);
		$this->addDpaOtherConfiguration($form);
		$form->addSubmit('save', 'save')
			->setHtmlAttribute('class', 'ajax btn btn-primary');
		$form->addProtection('core.errors.form-timeout');
		$form->setDefaults($this->configuration);
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Adds RF configuration to the form
	 * @param Form $form IQRF RF configuration form
	 */
	private function addRfConfiguration(Form &$form): void {
		$form->addGroup('rf');
		$rfBands = ['433', '868', '916'];
		foreach ($rfBands as $key => $rfBand) {
			$rfBands[$rfBand] = 'rfBands.' . $rfBand;
			unset($rfBands[$key]);
		}
		$form->addSelect('rfBand', 'rfBand', $rfBands)->setDisabled();
		if (isset($this->configuration->rfBand)) {
			$form['rfBand']->setDefaultValue($this->configuration->rfBand);
		}
		$rfChannels = ['rfChannelA', 'rfChannelB'];
		foreach ($rfChannels as $rfChannel) {
			$input = $form->addInteger($rfChannel, $rfChannel);
			$this->setRfChannelRule($input);
		}
		$subChannels = ['rfSubChannelA', 'rfSubChannelB', 'rfAltDsmChannel'];
		foreach ($subChannels as $subChannel) {
			if (isset($this->configuration->$subChannel)) {
				$input = $form->addInteger($subChannel, $subChannel);
				$this->setRfChannelRule($input);
			}
		}
		if (isset($this->configuration->stdAndLpNetwork)) {
			$warning = $form->getTranslator()->translate('messages.breakInteroperability');
			$form->addCheckbox('stdAndLpNetwork', 'stdAndLpNetwork')
				->setHtmlAttribute('data-warning', $warning);
		}
		$form->addInteger('txPower', 'txPower')
			->addRule(Form::RANGE, 'messages.txPower', [0, 7])
			->setRequired('messages.txPower');
		$form->addInteger('rxFilter', 'rxFilter')
			->addRule(Form::RANGE, 'messages.rxFilter', [0, 64])
			->setRequired('messages.rxFilter');
		$form->addInteger('lpRxTimeout', 'lpRxTimeout')
			->addRule(Form::RANGE, 'messages.lpRxTimeout', [1, 255])
			->setRequired('messages.lpRxTimeout');
	}

	/**
	 * Sets rules for RF channel input
	 * @param TextInput $input RF channel input
	 */
	private function setRfChannelRule(TextInput $input): void {
		$rfBand = $this->configuration->rfBand ?? null;
		switch ($rfBand) {
			case '443':
				$input->addRule(Form::RANGE, 'messages.rfChannel443', [0, 16]);
				break;
			case '868':
				$input->addRule(Form::RANGE, 'messages.rfChannel868', [0, 67]);
				break;
			case '916':
				$input->addRule(Form::RANGE, 'messages.rfChannel916', [0, 255]);
				break;
			default:
				$input->setDisabled();
				break;
		}
	}

	/**
	 * Adds RFPGM configuration to the form
	 * @param Form $form IQRF RF configuration form
	 */
	private function addRfpgwConfiguration(Form &$form): void {
		$form->addGroup('rfPgm');
		$form->addCheckbox('rfPgmEnableAfterReset', 'rfPgmEnableAfterReset');
		$form->addCheckbox('rfPgmTerminateAfter1Min', 'rfPgmTerminateAfter1Min');
		$form->addCheckbox('rfPgmTerminateMcuPin', 'rfPgmTerminateMcuPin');
		$form->addCheckbox('rfPgmDualChannel', 'rfPgmDualChannel');
		$form->addCheckbox('rfPgmLpMode', 'rfPgmLpMode');
		$form->addCheckbox('rfPgmIncorrectUpload', 'rfPgmIncorrectUpload')->setDisabled();
		if (isset($this->configuration->rfPgmIncorrectUpload)) {
			$form['rfPgmIncorrectUpload']->setDefaultValue($this->configuration->rfPgmIncorrectUpload);
		}
	}

	/**
	 * Adds embedded peripherals to the form
	 * @param Form $form DPA configuration form
	 */
	private function addDpaEmbeddedPeripherals(Form &$form): void {
		$form->addGroup('dpaEmbeddedPeripherals');
		$embeddedPeripherals = $form->addContainer('embPers');
		$unchangeablePeripherals = ['coordinator', 'node', 'os'];
		if ($this->presenter->getUser()->isInRole('power')) {
			foreach ($unchangeablePeripherals as $peripheral) {
				$embeddedPeripherals->addCheckbox($peripheral, 'embPers.' . $peripheral)
					->setDisabled();
				if (isset($this->configuration->embPers)) {
					$embeddedPeripherals[$peripheral]->setValue($this->configuration->embPers->$peripheral);
				}
			}
		}
		$changeablePeripherals = ['eeprom', 'eeeprom', 'ram', 'ledr', 'ledg', 'spi', 'io', 'thermometer', 'uart'];
		foreach ($changeablePeripherals as $peripheral) {
			$embeddedPeripherals->addCheckbox($peripheral, 'embPers.' . $peripheral);
		}
		$dpaVersion = $this->cache->load('dpaVersion' . $this->address) ?? '99.99';
		if (version_compare($dpaVersion, '4.00', '<')) {
			$embeddedPeripherals->addCheckbox('frc', 'embPers.frc');
		}
	}

	/**
	 * Add other configuration to the form
	 * @param Form $form DPA configuration form
	 */
	private function addDpaOtherConfiguration(Form &$form): void {
		$form->addGroup('dpaOther');
		$dpaCustomHandler = $form->addCheckbox('customDpaHandler', 'customDpaHandler');
		if ($this->cache->load('dpaHandlerDetected' . $this->address) !== true) {
			$dpaCustomHandler->setDisabled();
		}
		$form->addCheckbox('ioSetup', 'ioSetup');
		$form->addCheckbox('dpaAutoexec', 'dpaAutoexec');
		$form->addCheckbox('routingOff', 'routingOff');
		$form->addCheckbox('peerToPeer', 'peerToPeer');
		if (isset($this->configuration->dpaPeerToPeer)) {
			$form->addCheckbox('dpaPeerToPeer', 'dpaPeerToPeer');
		}
		if (isset($this->configuration->neverSleep)) {
			$form->addCheckbox('neverSleep', 'neverSleep');
		}
		$uartBaudrates = [1200, 2400, 4800, 9600, 19200, 38400, 57600, 115200, 230400];
		foreach ($uartBaudrates as $key => $baudrate) {
			$uartBaudrates[$baudrate] = 'uartBaudrates.' . $baudrate;
			unset($uartBaudrates[$key]);
		}
		$form->addSelect('uartBaudrate', 'uartBaudrate', $uartBaudrates);
		if (isset($this->configuration->nodeDpaInterface)) {
			$form->addCheckbox('nodeDpaInterface', 'nodeDpaInterface');
		}
	}

	/**
	 * Writes IQRF TR configuration from the form
	 * @param Form $form Set TR configuration form
	 */
	public function save(Form $form): void {
		$config = $form->getValues('array');
		if (array_key_exists('stdAndLpNetwork', $config)) {
			$config['stdAndLpNetwork'] = (bool) $config['stdAndLpNetwork'];
		}
		try {
			$this->manager->write($this->address, $config);
			$this->cache->remove('trConfiguration' . $this->address);
			$this->presenter->flashSuccess('iqrfnet.trConfiguration.write.success');
		} catch (DpaErrorException | EmptyResponseException | JsonException | UserErrorException $e) {
			$this->presenter->flashError('iqrfnet.trConfiguration.write.failure');
		}
	}

}
