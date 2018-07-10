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

namespace App\Presenters;

use App\Model\VersionManager;
use Kdyby\Translation\Phrase;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Presenter;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter {

	/**
	 * @persistent
	 * @var string Language
	 */
	public $lang;

	/**
	 * @var Translator Translator
	 */
	protected $translator;

	/**
	 * @var VersionManager Version manager
	 * @inject
	 */
	public $versionManager;

	/**
	 * Only for administrators
	 */
	public function onlyForAdmins() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect(':Sign:in');
		}
	}

	/**
	 * After template render
	 */
	public function afterRender() {
		parent::afterRender();
		if ($this->versionManager->availableWebappUpdate()) {
			$version = ['version' => $this->versionManager->getCurrentWebapp()];
			$phrase = new Phrase('core.update.available-webapp', null, $version);
			$this->flashMessage($phrase, 'danger');
		}
	}

	/**
	 * Inject translator service
	 * @param Translator Translator
	 */
	public function injectTranslator(Translator $translator) {
		$this->translator = $translator;
	}

	public function createTemplate($class = null) {
		$template = parent::createTemplate($class);
		$template->setTranslator($this->translator);
		return $template;
	}

}
