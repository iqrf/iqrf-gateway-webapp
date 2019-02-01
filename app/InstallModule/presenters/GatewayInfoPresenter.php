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

namespace App\InstallModule\Presenters;

use App\GatewayModule\Models\InfoManager;
use Nette\Utils\JsonException;

/**
 * Gateway info presenter
 */
class GatewayInfoPresenter extends InstallationPresenter {

	/**
	 * @var InfoManager GW Info manager
	 */
	private $infoManager;

	/**
	 * Constructor
	 * @param InfoManager $infoManager GW Info manager
	 */
	public function __construct(InfoManager $infoManager) {
		$this->infoManager = $infoManager;
		parent::__construct();
	}

	/**
	 * Render default page
	 * @throws JsonException
	 */
	public function renderDefault(): void {
		$this->template->ipAddresses = $this->infoManager->getIpAddresses();
		$this->template->macAddresses = $this->infoManager->getMacAddresses();
		$this->template->board = $this->infoManager->getBoard();
		$this->template->hostname = $this->infoManager->getHostname();
		$this->template->daemonVersion = $this->infoManager->getDaemonVersion();
		$this->template->webAppVersion = $this->infoManager->getWebAppVersion();
		$this->template->gwId = $this->infoManager->getId();
		$this->template->gwmonId = $this->infoManager->getGwmonId();
	}

}
