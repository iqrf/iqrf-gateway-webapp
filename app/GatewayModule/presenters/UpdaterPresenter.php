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

namespace App\GatewayModule\Presenters;

use App\CoreModule\Presenters\ProtectedPresenter;
use App\GatewayModule\Models\UpdaterManager;

/**
 * IQRF Gateway updater presenter
 */
class UpdaterPresenter extends ProtectedPresenter {

	/**
	 * @var UpdaterManager Updater manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param UpdaterManager $manager Updater manager
	 */
	public function __construct(UpdaterManager $manager) {
		$this->manager = $manager;
		parent::__construct();
	}

	/**
	 * Handles listing of upgradable packages
	 */
	public function handleList(): void {
		$this->manager->listUpgradable([$this, 'showCommandOutput']);
	}

	/**
	 * Handles updating package's cache
	 */
	public function handleUpdate(): void {
		$this->manager->update([$this, 'showCommandOutput']);
	}

	/**
	 * Handles updating packages
	 */
	public function handleUpgrade(): void {
		$this->manager->upgrade([$this, 'showCommandOutput']);
	}

	/**
	 * Shows shell command output
	 * @param string $type Output type
	 * @param string|null $buffer Output buffer
	 */
	public function showCommandOutput(string $type, ?string $buffer): void {
		if (isset($this->template->outputBuffer)) {
			$this->template->outputBuffer .= $buffer;
		} else {
			$this->template->outputBuffer = $buffer;
		}
		$this->redrawControl('outputChange');
	}

}
