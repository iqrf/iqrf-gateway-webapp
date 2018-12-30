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
		$this->template->iqrfGw = $this->context->parameters['iqrf-gw'];
		$this->template->supervisord = $this->context->parameters['supervisord'];
	}

	/**
	 * Inject translator service
	 * @param Translator $translator Translator
	 */
	public function injectTranslator(Translator $translator): void {
		$this->translator = $translator;
	}

	/**
	 * Create an template
	 * @return ITemplate Template
	 */
	public function createTemplate(): ITemplate {
		$template = parent::createTemplate();
		$template->setTranslator($this->translator);
		return $template;
	}

}
