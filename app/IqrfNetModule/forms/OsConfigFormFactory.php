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

use App\IqrfNetModule\Presenters\OsConfigPresenter;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\SmartObject;

/**
 * IQRF TR - IQRF OS configuration form factory
 */
class OsConfigFormFactory extends TrConfigFormFactory {

	use SmartObject;

	/**
	 * Create IQRF OS configuration tool form
	 * @param OsConfigPresenter $presenter IQRF OS configuration presenter
	 * @return Form IQRF OS configuration tool form
	 */
	public function create(OsConfigPresenter $presenter): Form {
		$this->presenter = $presenter;
		$this->load();
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('iqrfnet.osConfig'));
		$this->addRfConfiguration($form);
		$this->addRfpgwConfiguration($form);
		$form->addSubmit('save', 'save');
		$form->addProtection('core.errors.form-timeout');
		$form->setDefaults($this->configuration);
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Add RF configuration to the form
	 * @param Form $form IQRF OS configuration form
	 */
	private function addRfConfiguration(Form &$form): void {
		$rfBands = ['433', '868', '916'];
		foreach ($rfBands as $key => $rfBand) {
			$rfBands[$rfBand] = 'rfBands.' . $rfBand;
			unset($rfBands[$key]);
		}
		$form->addSelect('rfBand', 'rfBand', $rfBands)->setDisabled();
		if (array_key_exists('rfBand', $this->configuration)) {
			$form['rfBand']->setDefaultValue($this->configuration['rfBand']);
		}
		$rfChannels = ['rfChannelA', 'rfChannelB'];
		foreach ($rfChannels as $rfChannel) {
			$form->addInteger($rfChannel, $rfChannel);
			$this->setRfChannelRule($form[$rfChannel]);
		}
		$subChannels = ['rfSubChannelA', 'rfSubChannelB'];
		foreach ($subChannels as $subChannel) {
			if (array_key_exists($subChannel, $this->configuration)) {
				$form->addInteger($subChannel, $subChannel);
				$this->setRfChannelRule($form[$subChannel]);
			}
		}
		if (array_key_exists('stdAndLpNetwork', $this->configuration)) {
			$networkTypes = [false => 'networkTypes.std', true => 'networkTypes.stdLp'];
			$form->addSelect('stdAndLpNetwork', 'networkType', $networkTypes);
		}
		$form->addInteger('txPower', 'txPower')->addRule(Form::RANGE, 'messages.txPower', [0, 7])
			->setRequired('messages.txPower');
		$form->addInteger('rxFilter', 'rxFilter')->addRule(Form::RANGE, 'messages.rxFilter', [0, 64])
			->setRequired('messages.rxFilter');
		$form->addInteger('lpRxTimeout', 'lpRxTimeout')->addRule(Form::RANGE, 'messages.lpRxTimeout', [1, 255])
			->setRequired('messages.lpRxTimeout');
	}

	/**
	 * Add RFPGM configuration to the form
	 * @param Form $form IQRF OS configuration form
	 */
	private function addRfpgwConfiguration(Form &$form): void {
		$form->addCheckbox('rfPgmEnableAfterReset', 'rfPgmEnableAfterReset');
		$form->addCheckbox('rfPgmTerminateAfter1Min', 'rfPgmTerminateAfter1Min');
		$form->addCheckbox('rfPgmTerminateMcuPin', 'rfPgmTerminateMcuPin');
		$form->addCheckbox('rfPgmDualChannel', 'rfPgmDualChannel');
		$form->addCheckbox('rfPgmLpMode', 'rfPgmLpMode');
		$form->addCheckbox('rfPgmIncorrectUpload', 'rfPgmIncorrectUpload')->setDisabled();
		if (array_key_exists('rfPgmIncorrectUpload', $this->configuration)) {
			$form['rfPgmIncorrectUpload']->setDefaultValue($this->configuration['rfPgmIncorrectUpload']);
		}
	}

	/**
	 * Set rules for RF channel input
	 * @param TextInput $input RF channel input
	 */
	private function setRfChannelRule(TextInput $input): void {
		$rfBand = $this->configuration['rfBand'] ?? null;
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

}
