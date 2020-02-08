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
use App\GatewayModule\Models\DiagnosticsManager;
use App\GatewayModule\Models\InfoManager;
use App\GatewayModule\Models\NetworkManager;
use App\GatewayModule\Models\VersionManager;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Models\GwModeManager;
use Nette\Application\BadRequestException;
use Nette\Utils\JsonException;
use Tracy\Debugger;

/**
 * IQRF Gateway Info presenter
 */
class InfoPresenter extends ProtectedPresenter {

	/**
	 * @var DiagnosticsManager IQRF Gateway Diagnostic manager
	 */
	private $diagnosticsManager;

	/**
	 * @var InfoManager IQRF Gateway Info manager
	 */
	private $infoManager;

	/**
	 * @var GwModeManager IQRF Gateway Daemon's mode manager
	 */
	private $gwModeManager;

	/**
	 * @var NetworkManager Network manager
	 */
	private $networkManager;

	/**
	 * @var VersionManager Version manager
	 */
	private $swVersionManager;

	/**
	 * Constructor
	 * @param InfoManager $infoManager IQRF Gateway Info manager
	 * @param DiagnosticsManager $diagnosticsManager IQRF Gateway Diagnostic manager
	 * @param GwModeManager $gwModeManager IQRF Gateway Daemon's mode manager
	 * @param NetworkManager $networkManager Network manager
	 * @param VersionManager $versionManager Version manager
	 */
	public function __construct(InfoManager $infoManager, DiagnosticsManager $diagnosticsManager, GwModeManager $gwModeManager, NetworkManager $networkManager, VersionManager $versionManager) {
		$this->diagnosticsManager = $diagnosticsManager;
		$this->infoManager = $infoManager;
		$this->gwModeManager = $gwModeManager;
		$this->networkManager = $networkManager;
		$this->swVersionManager = $versionManager;
		parent::__construct();
	}

	/**
	 * Renders IQRF Gateway Info page
	 * @throws JsonException
	 */
	public function renderDefault(): void {
		$this->template->ipAddresses = $this->networkManager->getIpAddresses();
		$this->template->macAddresses = $this->networkManager->getMacAddresses();
		$this->template->board = $this->infoManager->getBoard();
		$this->template->hostname = $this->networkManager->getHostname();
		$this->template->controllerVersion = $this->swVersionManager->getController();
		$this->template->daemonVersion = $this->swVersionManager->getDaemon();
		$this->template->webAppVersion = $this->swVersionManager->getWebapp();
		$this->template->diskUsages = $this->infoManager->getDiskUsages();
		$this->template->memoryUsage = $this->infoManager->getMemoryUsage();
		$this->template->swapUsage = $this->infoManager->getSwapUsage();
		$this->template->gwId = $this->infoManager->getId();
		$this->template->gwmodId = $this->infoManager->getPixlaToken();
		try {
			$this->template->module = $this->infoManager->getCoordinatorInfo()['response']->data->rsp;
		} catch (DpaErrorException | EmptyResponseException $e) {
			$this->flashError('gateway.info.tr.error');
		}
		try {
			$this->template->gwMode = $this->gwModeManager->get();
		} catch (DpaErrorException | EmptyResponseException $e) {
			$this->template->gwMode = 'unknown';
		}
	}

	/**
	 * Handles download IQRF Gateway Daemon's log action
	 * @throws JsonException
	 */
	public function actionDownload(): void {
		try {
			$this->sendResponse($this->diagnosticsManager->download());
		} catch (BadRequestException $e) {
			Debugger::log('Cannot read zip archive with diagnostic data.');
			$this->redirect('Info:default');
		}
	}

}
