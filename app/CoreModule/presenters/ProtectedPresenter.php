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

use App\CoreModule\Model\VersionManager;
use GuzzleHttp\Exception\TransferException;
use Kdyby\Translation\Phrase;
use Nette\Utils\JsonException;

/**
 * Protected presenter for protected application presenters
 */
abstract class ProtectedPresenter extends BasePresenter {

	/**
	 * @var VersionManager Version manager
	 * @inject
	 */
	public $versionManager;

	/**
	 * After template render
	 * @throws JsonException
	 */
	public function afterRender(): void {
		parent::afterRender();
		try {
			if ($this->versionManager->availableWebappUpdate()) {
				$version = ['version' => $this->versionManager->getCurrentWebapp()];
				$phrase = new Phrase('core.update.available-webapp', null, $version);
				$this->flashMessage($phrase, 'danger');
			}
		} catch (TransferException $e) {
			$this->flashMessage('core.update.error', 'warning');
		}
	}

	/**
	 * Start up an protected presenter
	 */
	protected function startup(): void {
		parent::startup();
		if (!$this->user->isLoggedIn()) {
			$this->redirect(':Core:Sign:in');
		}
	}

}
