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
use App\IqrfNetModule\Presenters\OsConfigPresenter;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\JsonException;

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
		try {
			$this->configuration = $this->load();
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->configuration = [];
		}
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

}
