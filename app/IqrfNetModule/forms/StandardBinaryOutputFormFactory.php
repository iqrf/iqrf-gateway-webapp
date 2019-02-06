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
use App\IqrfNetModule\Entities\StandardBinaryOutput;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Models\StandardBinaryOutputManager;
use App\IqrfNetModule\Presenters\StandardPresenter;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * IQRF Standard binary output form factory
 */
class StandardBinaryOutputFormFactory {

	use SmartObject;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var StandardPresenter IQRF Standard presenter
	 */
	private $presenter;

	/**
	 * @var StandardBinaryOutputManager IQRF Standard binary output manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param StandardBinaryOutputManager $manager IQRF Standard binary output manager
	 */
	public function __construct(FormFactory $factory, StandardBinaryOutputManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates IQRF Standard Binary output form
	 * @param StandardPresenter $presenter IQRF Standard presenter
	 * @return Form IQRF Standard Binary output form
	 */
	public function create(StandardPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('iqrfnet.standard.binaryOutput'));
		$form->addInteger('address', 'address')->setRequired('messages.address');
		$form->addInteger('index', 'index')->setDefaultValue(0)
			->addRule(Form::RANGE, 'messages.index', [0, 31]);
		$form->addCheckbox('state', 'state');
		$form->addSubmit('enumerate', 'enumerate')->onClick[] = [$this, 'enumerate'];
		$form->addSubmit('get', 'get')->onClick[] = [$this, 'get'];
		$form->addSubmit('set', 'set')->onClick[] = [$this, 'set'];
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

	/**
	 * Enumerates a standard binary output
	 * @param SubmitButton $button Submit button
	 */
	public function enumerate(SubmitButton $button): void {
		$values = $button->getForm()->getValues(true);
		try {
			$data = $this->manager->enumerate($values['address']);
			$this->presenter->handleBinaryOutputResponse($data);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->presenter->flashMessage('iqrfnet.standard.binaryOutput.messages.enumerateError', 'danger');
		}
	}

	/**
	 * Gets output's state of a standard binary output
	 * @param SubmitButton $button Submit button
	 */
	public function get(SubmitButton $button): void {
		$values = $button->getForm()->getValues(true);
		try {
			$data = $this->manager->getOutputs($values['address']);
			$this->presenter->handleBinaryOutputResponse($data);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->presenter->flashMessage('iqrfnet.standard.binaryOutput.messages.getError', 'danger');
		}
	}

	/**
	 * Sets output's state of a standard binary output
	 * @param SubmitButton $button Submit button
	 */
	public function set(SubmitButton $button): void {
		$values = $button->getForm()->getValues(true);
		try {
			$output = new StandardBinaryOutput($values['index'], $values['state']);
			$data = $this->manager->setOutputs($values['address'], [$output]);
			$this->presenter->handleBinaryOutputResponse($data);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->presenter->flashMessage('iqrfnet.standard.binaryOutput.messages.setError', 'danger');
		}
	}

}
