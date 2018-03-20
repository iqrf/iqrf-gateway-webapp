<?php

/**
 * Copyright 2017 IQRF Tech s.r.o.
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
declare(strict_types=1);

namespace App\IqrfAppModule\Forms;

use App\Forms\FormFactory;
use App\IqrfAppModule\Model\EmptyResponseException;
use App\IqrfAppModule\Model\IqrfNetManager;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;

/**
 * IQMESH RF form factory.
 */
class IqrfNetRfFormFactory {

	use Nette\SmartObject;

	/**
	 * @var IqrfNetManager IQMESH Network manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var string RF Band
	 */
	private $rfBand;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param IqrfNetManager $manager IQMESH Network manager
	 */
	public function __construct(FormFactory $factory, IqrfNetManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Create IQMESH Access password form
	 * @return Form IQMESH Access password form
	 */
	public function create(): Form {
		$form = $this->factory->create();
		$rfBands = [
			'443 MHz' => '443 MHz',
			'868 MHz' => '868 MHz',
			'916 MHz' => '916 MHz',
			'ERROR' => 'ERROR',
		];
		$types = [
			IqrfNetManager::MAIN_RF_CHANNEL_A => 'Main Channel A',
			IqrfNetManager::MAIN_RF_CHANNEL_B => 'Main Channel B',
			IqrfNetManager::ALTERNATIVE_RF_CHANNEL_A => 'Alternative Channel A',
			IqrfNetManager::ALTERNATIVE_RF_CHANNEL_B => 'Alternative Channel B',
		];
		try {
			$this->rfBand = $this->manager->readHwpConfiguration()['rfBand'] ?? 'ERROR';
		} catch (EmptyResponseException $e) {
			$this->rfBand = 'ERROR';
		}
		$form->addSelect('rfBand', 'RF Band', $rfBands)->setDisabled()
				->setDefaultValue($this->rfBand);
		$form->addInteger('rfChannel', 'RF Channel')
				->setRequired(true)
				->addConditionOn($form['rfBand'], Form::EQUAL, '443 MHz')
				->addRule(Form::RANGE, 'RF Channel has to be in range from 0 to 16.', [0, 16])
				->elseCondition($form['rfBand'], Form::EQUAL, '868 MHz')
				->addRule(Form::RANGE, 'RF Channel has to be in range from 0 to 67.', [0, 67])
				->elseCondition($form['rfBand'], Form::EQUAL, '916 MHz')
				->addRule(Form::RANGE, 'RF Channel has to be in range from 0 to 255.', [0, 255]);
		$form->addSelect('type', 'RF Channel type', $types);
		$form->addSubmit('set', 'Set')->onClick[] = [$this, 'setChannel'];
		$form->addProtection('Timeout expired, resubmit the form.');
		return $form;
	}

	/**
	 * Set RF channel
	 * @param SubmitButton $button Submit button for setting RF channel
	 */
	public function setChannel(SubmitButton $button) {
		$form = $button->getForm();
		$values = $form->getValues();
		if ($this->rfBand === 'ERROR') {
			$form->addError('Invalid RF Band.');
		} else {
			$this->manager->setRfChannel($values['rfChannel'], strval($values['type']));
		}
	}

}
