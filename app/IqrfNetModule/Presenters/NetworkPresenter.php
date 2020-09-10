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

namespace App\IqrfNetModule\Presenters;

use App\CoreModule\Presenters\ProtectedPresenter;
use App\IqrfNetModule\Models\DevicesManager;

/**
 * IQMESH Network manager presenter
 */
class NetworkPresenter extends ProtectedPresenter {

	/**
	 * @var DevicesManager Bonded and Discovered devices manager
	 */
	private $devicesManager;

	/**
	 * Constructor
	 * @param DevicesManager $devicesManager Bonded and discovered devices manager
	 */
	public function __construct(DevicesManager $devicesManager) {
		$this->devicesManager = $devicesManager;
		parent::__construct();
	}

	/**
	 * Shows table with devices
	 * @param bool $ping Perform ping?
	 */
	public function handleShowNodes(bool $ping = false): void {
		$base = 10;
		$this->template->base = $base;
		$this->template->devices = $this->devicesManager->getTable($base, $ping);
		$this->redrawControl('devicesWrapper');
		$this->redrawControl('showDevices');
	}

	/**
	 * Renders a default page
	 */
	public function renderDefault(): void {
		if (!$this->isAjax()) {
			$this->handleShowNodes();
		}
	}

}
