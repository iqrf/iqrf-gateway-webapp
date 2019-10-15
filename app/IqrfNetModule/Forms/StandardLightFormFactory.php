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
use App\IqrfNetModule\Entities\StandardLight;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Models\StandardLightManager;
use App\IqrfNetModule\Presenters\StandardPresenter;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * IQRF Standard light form factory
 */
class StandardLightFormFactory {

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
	 * @var StandardLightManager IQRF Standard light manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param StandardLightManager $manager IQRF Standard light manager
	 */
	public function __construct(FormFactory $factory, StandardLightManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates IQRF Standard light form
	 * @param StandardPresenter $presenter IQRF Standard presenter
	 * @return Form IQRF Standard light form
	 */
	public function create(StandardPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('iqrfnet.standard.light');
		$form->addInteger('address', 'address')
			->setRequired('messages.address');
		$form->addInteger('index', 'index')
			->setDefaultValue(0)
			->addRule(Form::RANGE, 'messages.index', [0, 31]);
		$form->addInteger('power', 'power')
			->setDefaultValue(50)
			->addRule(Form::RANGE, 'messages.power', [0, 100]);
		$form->addSubmit('enumerate', 'enumerate')
			->setHtmlAttribute('class', 'ajax')
			->onClick[] = [$this, 'enumerate'];
		$form->addSubmit('get', 'get')
			->setHtmlAttribute('class', 'ajax')
			->onClick[] = [$this, 'getPower'];
		$form->addSubmit('set', 'set')
			->setHtmlAttribute('class', 'ajax')
			->onClick[] = [$this, 'setPower'];
		$form->addSubmit('increment', 'increment')
			->setHtmlAttribute('class', 'ajax')
			->onClick[] = [$this, 'incrementPower'];
		$form->addSubmit('decrement', 'decrement')
			->setHtmlAttribute('class', 'ajax')
			->onClick[] = [$this, 'decrementPower'];
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

	/**
	 * Enumerates a IQRF Standard light device
	 * @param SubmitButton $button Submit button
	 */
	public function enumerate(SubmitButton $button): void {
		$values = $button->getForm()->getValues();
		try {
			$data = $this->manager->enumerate($values->address);
			$this->presenter->handleLightResponse($data);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->presenter->flashError('iqrfnet.standard.light.messages.enumerateError');
		}
	}

	/**
	 * Gets a power of IQRF Standard light
	 * @param SubmitButton $button Submit button
	 */
	public function getPower(SubmitButton $button): void {
		$values = $button->getForm()->getValues();
		try {
			$light = new StandardLight($values->index, $values->power);
			$data = $this->manager->getPower($values->address, [$light]);
			$this->presenter->handleLightResponse($data);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->presenter->flashError('iqrfnet.standard.light.messages.getError');
		}
	}

	/**
	 * Sets a power of IQRF Standard light
	 * @param SubmitButton $button Submit button
	 */
	public function setPower(SubmitButton $button): void {
		$values = $button->getForm()->getValues();
		try {
			$light = new StandardLight($values->index, $values->power);
			$data = $this->manager->setPower($values->address, [$light]);
			$this->presenter->handleLightResponse($data);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->presenter->flashError('iqrfnet.standard.light.messages.setError');
		}
	}

	/**
	 * Increments a power of IQRF Standard light
	 * @param SubmitButton $button Submit button
	 */
	public function incrementPower(SubmitButton $button): void {
		$values = $button->getForm()->getValues();
		try {
			$light = new StandardLight($values->index, $values->power);
			$data = $this->manager->incrementPower($values->address, [$light]);
			$this->presenter->handleLightResponse($data);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->presenter->flashError('iqrfnet.standard.light.messages.incrementError');
		}
	}

	/**
	 * Decrements a power of IQRF Standard light
	 * @param SubmitButton $button Submit button
	 */
	public function decrementPower(SubmitButton $button): void {
		$values = $button->getForm()->getValues();
		try {
			$light = new StandardLight($values->index, $values->power);
			$data = $this->manager->decrementPower($values->address, [$light]);
			$this->presenter->handleLightResponse($data);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->presenter->flashError('iqrfnet.standard.light.messages.decrementError');
		}
	}

}
