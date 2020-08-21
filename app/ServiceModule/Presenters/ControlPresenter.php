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

namespace App\ServiceModule\Presenters;

use App\CoreModule\Presenters\ProtectedPresenter;
use App\CoreModule\Traits\TPresenterFlashMessage;

/**
 * Service control presenter.
 */
class ControlPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

	/**
	 * Renders service status
	 * @var string $name Service name
	 */
	public function renderDefault(string $name): void {
		$this->template->service = $name;
	}

	/**
	 * Checks if the service is whitelisted
	 * @param string $name Service name
	 */
	public function actionDefault(string $name): void {
		if ($name === 'unattended-upgrades' && !$this->featureManager->isEnabled('unattendedUpgrades') ||
			$name === 'ssh' && !$this->featureManager->isEnabled('ssh')) {
			$this->flashError('service.' . $name . '.messages.disabled');
			$this->redirect(':Gateway:Homepage:default');
		}
	}

}
