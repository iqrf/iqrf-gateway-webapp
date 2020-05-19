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

namespace App\CoreModule\Forms;

use Contributte\Forms\Rendering\Bootstrap3VerticalRenderer;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;

/**
 * Generic form factory
 */
class FormFactory {

	/**
	 * @var ITranslator Translator
	 */
	private $translator;

	/**
	 * Constructor
	 * @param ITranslator $translator Translator service
	 */
	public function __construct(ITranslator $translator) {
		$this->translator = $translator;
	}

	/**
	 * Creates a form and set the translator
	 * @param string|null $translationPrefix Translated message prefix
	 * @return Form Form
	 */
	public function create(?string $translationPrefix = null): Form {
		$form = new Form();
		$renderer = new Bootstrap3VerticalRenderer();
		if ($translationPrefix === null) {
			$form->setTranslator($this->translator);
		} else {
			$translator = $this->translator->createPrefixedTranslator($translationPrefix);
			$form->setTranslator($translator);
		}
		$form->setRenderer($renderer);
		return $form;
	}

	/**
	 * Returns the translator service
	 * @return ITranslator Translator service
	 */
	public function getTranslator(): ITranslator {
		return $this->translator;
	}

}
