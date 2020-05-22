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
use Nette\Utils\JsonException;

/**
 * Core disambiguation presenter
 */
class HomepagePresenter extends ProtectedPresenter {

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
		if (!$this->featureManager->isEnabled('versionChecker')) {
			return;
		}
		try {
			if ($this->versionManager->availableWebappUpdate()) {
				$version = ['version' => $this->versionManager->getCurrentWebapp()];
				$message = $this->getTranslator()->translate('core.update.available-webapp', null, $version);
				$this->flashError($message);
			}
		} catch (TransferException $e) {
			$this->flashWarning('core.update.error');
		}
	}

}
