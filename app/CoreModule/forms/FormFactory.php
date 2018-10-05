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

namespace App\CoreModule\Forms;

use Instante\ExtendedFormMacros\IFormFactory;
use Kdyby\Translation\Translator;
use Nette\Forms\Form;
use Nette\SmartObject;

/**
 * Generic form factory
 */
class FormFactory {

	use SmartObject;

	/**
	 * @var IFormFactory Form factory interface
	 */
	public $iFormFactory;

	/**
	 * @var Translator Translator
	 */
	private $translator;

	/**
	 * Constructor
	 * @param Translator $translator Translator
	 * @param IFormFactory $iFormFactory Form factory interface
	 */
	public function __construct(Translator $translator, IFormFactory $iFormFactory) {
		$this->iFormFactory = $iFormFactory;
		$this->translator = $translator;
	}

	/**
	 * Create a form and set the translator
	 * @return Form Form
	 */
	public function create(): Form {
		$form = $this->iFormFactory->create();
		$form->setTranslator($this->translator);
		return $form;
	}

}
