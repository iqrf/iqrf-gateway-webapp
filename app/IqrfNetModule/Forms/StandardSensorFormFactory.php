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
use App\IqrfNetModule\Models\StandardSensorManager;
use App\IqrfNetModule\Presenters\StandardPresenter;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * IQRF Standard sensor form factory
 */
class StandardSensorFormFactory {

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
	 * @var StandardSensorManager IQRF Standard sensor manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param StandardSensorManager $manager IQRF Standard sensor manager
	 */
	public function __construct(FormFactory $factory, StandardSensorManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates IQRF Standard sensor form
	 * @param StandardPresenter $presenter IQRF Standard presenter
	 * @return Form IQRF Standard sensor form
	 */
	public function create(StandardPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('iqrfnet.standard.sensor');
		$form->addInteger('address', 'address')
			->setRequired('messages.address');
		$form->addSubmit('enumerate', 'enumerate')
			->setHtmlAttribute('class', 'ajax')
			->onClick[] = [$this, 'enumerate'];
		$form->addSubmit('readAll', 'readAll')
			->setHtmlAttribute('class', 'ajax')
			->onClick[] = [$this, 'readAll'];
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

	/**
	 * Enumerates a IQRF Standard sensor device
	 * @param SubmitButton $button Submit button
	 */
	public function enumerate(SubmitButton $button): void {
		$values = $button->getForm()->getValues();
		try {
			$data = $this->manager->enumerate($values->address);
			$this->presenter->handleSensorResponse($data);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->presenter->flashError('iqrfnet.standard.sensor.messages.enumerateError');
		}
	}

	/**
	 * Reads all sensors with types
	 * @param SubmitButton $button Submit button
	 */
	public function readAll(SubmitButton $button): void {
		$values = $button->getForm()->getValues();
		try {
			$data = $this->manager->readAll($values->address);
			$this->presenter->handleSensorResponse($data);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->presenter->flashError('iqrfnet.standard.sensor.messages.readError');
		}
	}

}
