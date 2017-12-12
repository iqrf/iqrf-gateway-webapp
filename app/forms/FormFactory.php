<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
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

namespace App\Forms;

use GettextTranslator\Gettext;
use Instante\ExtendedFormMacros\IFormFactory;
use Nette;
use Nette\Application\UI\Form;

/**
 * Form factory.
 */
class FormFactory {

	use Nette\SmartObject;

	/**
	 * @var IFormFactory
	 */
	public $iFormFactory;

	/**
	 * @var Gettext
	 */
	private $translator;

	/**
	 * Constructor
	 * @param Gettext $translator
	 * @param IFormFactory $iFormFactory
	 */
	public function __construct(Gettext $translator, IFormFactory $iFormFactory) {
		$this->iFormFactory = $iFormFactory;
		$this->translator = $translator;
	}

	/**
	 * @return Form
	 */
	public function create() {
		$form = $this->iFormFactory->create();
		$form->setTranslator($this->translator);
		return $form;
	}

}
