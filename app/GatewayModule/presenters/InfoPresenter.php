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

namespace App\GatewayModule\Presenters;

use App\GatewayModule\Model\DiagnosticsManager;
use App\GatewayModule\Model\InfoManager;
use App\IqrfAppModule\Exception\EmptyResponseException;
use App\IqrfAppModule\Exception\DpaErrorException;
use App\Presenters\ProtectedPresenter;
use Nette\Application\BadRequestException;
use Tracy\Debugger;

/**
 * Gateway Info presenter
 */
class InfoPresenter extends ProtectedPresenter {

	/**
	 * @var DiagnosticsManager GW Diagnostic manager
	 */
	private $diagnosticsManager;

	/**
	 * @var InfoManager GW Info manager
	 */
	private $infoManager;

	/**
	 * Constructor
	 * @param InfoManager $infoManager GW Info manager
	 * @param DiagnosticsManager $diagnosticsManager GW Diagnostic manager
	 */
	public function __construct(InfoManager $infoManager, DiagnosticsManager $diagnosticsManager) {
		$this->diagnosticsManager = $diagnosticsManager;
		$this->infoManager = $infoManager;
		parent::__construct();
	}

	/**
	 * Render default page
	 */
	public function renderDefault() {
		$this->template->ipAddresses = $this->infoManager->getIpAddresses();
		$this->template->macAddresses = $this->infoManager->getMacAddresses();
		$this->template->board = $this->infoManager->getBoard();
		$this->template->hostname = $this->infoManager->getHostname();
		$this->template->daemonVersion = $this->infoManager->getDaemonVersion();
		$this->template->webAppVersion = $this->infoManager->getWebAppVersion();
		try {
			$this->template->module = $this->infoManager->getCoordinatorInfo();
		} catch (\Exception $e) {
			if ($e instanceof EmptyResponseException || $e instanceof DpaErrorException) {
				$this->presenter->flashMessage('Cannot get information about the Coordinator.', 'danger');
			} else {
				throw $e;
			}
		}
	}

	/**
	 * Download action
	 */
	public function actionDownload() {
		try {
			$this->sendResponse($this->diagnosticsManager->download());
		} catch (BadRequestException $e) {
			Debugger::log('Cannot read zip archive with diagnostic data.');
			$this->redirect('Info:default');
			$this->setView('default');
		}
	}

}
