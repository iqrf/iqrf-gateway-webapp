<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
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

namespace App\GatewayModule\Presenters;

use App\GatewayModule\Model\InfoManager;
use App\IqrfAppModule\Model\EmptyResponseException;
use App\Presenters\BasePresenter;
use Tracy\Debugger;

/**
 * Gateway Info presenter
 */
class InfoPresenter extends BasePresenter {

	/**
	 * @var GwInfoManager
	 */
	private $infoManager;

	/**
	 * Constructor
	 * @param InfoManager $infoManager
	 */
	public function __construct(InfoManager $infoManager) {
		$this->infoManager = $infoManager;
	}

	/**
	 * Render default page
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
		$this->template->ipAddresses = $this->infoManager->getIpAddresses();
		$this->template->macAddresses = $this->infoManager->getMacAddresses();
		$this->template->hostname = $this->infoManager->getHostname();
		$this->template->daemonVersion = $this->infoManager->getDaemonVersion();
		try {
			$this->template->module = $this->infoManager->getCoordinatorInfo();
		} catch (EmptyResponseException $e) {
			Debugger::log('Cannot get information about the Coordinator.');
		}
	}

}
