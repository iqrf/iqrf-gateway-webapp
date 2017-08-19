<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
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

namespace App\Presenters;

use GettextTranslator\Gettext;
use Nette\Application\UI\Presenter;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter {

	/**
	 * @persistent
	 * @var string
	 */
	public $lang;

	/** @var Gettext */
	protected $translator;

	/**
	 * Only for administrators
	 */
	public function onlyForAdmins() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect(':Sign:in');
		}
	}

	/**
	 * Inject translator service
	 * @param Gettext
	 */
	public function injectTranslator(Gettext $translator) {
		$this->translator = $translator;
	}

	public function createTemplate($class = null) {
		$template = parent::createTemplate($class);
		// if not set, the default language will be used
		if (!isset($this->lang)) {
			$this->lang = $this->translator->getLang();
		} else {
			$this->translator->setLang($this->lang);
		}
		$template->setTranslator($this->translator);
		return $template;
	}

}
