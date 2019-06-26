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

namespace App\CoreModule\Presenters;

use Kdyby\Translation\Translator;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Presenter;

/**
 * Base presenter for all application presenters
 */
abstract class BasePresenter extends Presenter {

	/**
	 * @var string Language
	 * @persistent
	 */
	public $lang;

	/**
	 * @var Translator Translator
	 */
	protected $translator;

	/**
	 * After template render
	 */
	public function afterRender(): void {
		parent::afterRender();
		$this->template->newVersion = null;
		$this->template->offlineMode = false;
		$this->template->features = $this->context->parameters['features'];
	}

	/**
	 * Injects the translator service
	 * @param Translator $translator Translator
	 */
	public function injectTranslator(Translator $translator): void {
		$this->translator = $translator;
	}

	/**
	 * Returns the translator
	 * @return Translator Transtalor
	 */
	public function getTranslator(): Translator {
		return $this->translator;
	}

	/**
	 * Creates an template
	 * @return ITemplate Template
	 */
	public function createTemplate(): ITemplate {
		$template = parent::createTemplate();
		$template->setTranslator($this->translator);
		return $template;
	}

}
