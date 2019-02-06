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
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
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
	 * Constructor
	 * @param InfoManager $infoManager IQRF Gateway Info manager
	 * @param DiagnosticsManager $diagnosticsManager IQRF Gateway Diagnostic manager
	 */
	public function __construct(InfoManager $infoManager, DiagnosticsManager $diagnosticsManager) {
		$this->diagnosticsManager = $diagnosticsManager;
		$this->infoManager = $infoManager;
		parent::__construct();
	}

	/**
	 * Renders IQRF Gateway Info page
	 * @throws JsonException
	 */
	public function renderDefault(): void {
		$this->template->ipAddresses = $this->infoManager->getIpAddresses();
		$this->template->macAddresses = $this->infoManager->getMacAddresses();
		$this->template->board = $this->infoManager->getBoard();
		$this->template->hostname = $this->infoManager->getHostname();
		$this->template->daemonVersion = $this->infoManager->getDaemonVersion();
		$this->template->webAppVersion = $this->infoManager->getWebAppVersion();
		$this->template->diskUsages = $this->infoManager->getDiskUsages();
		$this->template->memoryUsage = $this->infoManager->getMemoryUsage();
		$this->template->swapUsage = $this->infoManager->getSwapUsage();
		$this->template->gwId = $this->infoManager->getId();
		$this->template->gwmodId = $this->infoManager->getPixlaToken();
		try {
			$this->template->module = $this->infoManager->getCoordinatorInfo()['response']['data']['rsp'];
		} catch (DpaErrorException | EmptyResponseException $e) {
			$this->presenter->flashMessage('gateway.info.tr.error', 'danger');
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
			$this->setView('default');
		}
	}

}
