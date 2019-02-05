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
use App\IqrfNetModule\Models\StandardLightManager;
use App\IqrfNetModule\Presenters\StandardPresenter;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
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
	 * Create IQRF App send JSON request form
	 * @param StandardPresenter $presenter IQRF Standard presenter
	 * @return Form IQRF App send RAW packet form
	 */
	public function create(StandardPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('iqrfnet.standard.light'));
		$form->addInteger('address', 'address')->setRequired('messages.address');
		$form->addSubmit('enumerate', 'enumerate')->onClick[] = [$this, 'enumerate'];
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

	/**
	 * Enumerate a standard sensor
	 * @param SubmitButton $button Submit button
	 */
	public function enumerate(SubmitButton $button): void {
		$values = $button->getForm()->getValues(true);
		try {
			$data = $this->manager->enumerate($values['address']);
			$this->presenter->handleLightResponse($data);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->presenter->flashMessage('iqrfnet.standard.light.messages.enumerateError', 'danger');
		}
	}

}
