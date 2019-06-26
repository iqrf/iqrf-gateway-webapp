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

use App\CoreModule\Models\VersionManager;
use App\CoreModule\Traits\TPresenterFlashMessage;
use GuzzleHttp\Exception\TransferException;
use Nette\Reflection\ClassType;
use Nette\Reflection\Method;
use Nette\Security\IUserStorage;
use Nette\Utils\JsonException;

/**
 * Protected presenter for protected application presenters
 */
abstract class ProtectedPresenter extends BasePresenter {

	use TPresenterFlashMessage;

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
				$phrase = $this->getTranslator()->translate('core.update.new-version-tag', null, $version);
				$this->template->newVersion = $phrase;
			}
		} catch (TransferException $e) {
			$this->template->offlineMode = true;
		}
	}

	/**
	 * Checks requirements
	 * @param ClassType|Method $element Element
	 */
	public function checkRequirements($element): void {
		if (!$this->user->isLoggedIn()) {
			if ($this->user->getLogoutReason() === IUserStorage::INACTIVITY) {
				$this->flashInfo('core.signOut.inactivity');
			}
			$this->redirect(':Core:Sign:In', ['backlink' => $this->storeRequest()]);
		}
		parent::checkRequirements($element);
	}

}
