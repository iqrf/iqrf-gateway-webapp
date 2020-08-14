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

namespace App\CloudModule\Forms;

use App\CloudModule\Models\PixlaManager;
use App\CloudModule\Presenters\PixlaPresenter;
use App\CoreModule\Forms\FormFactory;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextInput;
use Nette\IOException;

class PixlaFormFactory {

	/**
	 * @var FormFactory $factory Generic form factory
	 */
	private $factory;

	/**
	 * @var PixlaManager $manager
	 */
	private $manager;

	/**
	 * @var PixlaPresenter PIXLA platform presenter
	 */
	private $presenter;

	/**
	 * PIXLA platform form prefix
	 */
	private const PREFIX = 'cloud.pixla.form';

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param PixlaManager $manager PIXLA platform manager
	 */
	public function __construct(FormFactory $factory, PixlaManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates the PIXLA platform form
	 * @param PixlaPresenter $presenter Presenter for the PIXLA platform
	 * @return Form PIXLA platform form
	 */
	public function create(PixlaPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create(self::PREFIX);
		$form->addText('token', 'token')
			->setRequired('missingParam');
		$form->addSubmit('save', 'save')
			->setHtmlAttribute('class', 'ajax btn btn-primary');
		$form->onSubmit[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Saves the new PIXLA platform token.
	 * @param Form $form Form
	 */
	public function save(Form $form): void {
		$token = $form->getValues()->token;
		try {
			$this->manager->setToken($token);
			$form->setValues([], true);
			$this->presenter->flashSuccess(self::PREFIX . '.editSuccess');
		} catch (IOException $e) {
			$this->presenter->flashError(self::PREFIX . '.editFailure');
		}
	}

}
