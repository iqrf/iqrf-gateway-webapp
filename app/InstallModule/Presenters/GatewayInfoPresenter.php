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
use App\GatewayModule\Models\NetworkManager;
use App\GatewayModule\Models\VersionManager;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use Nette\Utils\JsonException;

/**
 * IQRF Gateway info presenter
 */
class GatewayInfoPresenter extends InstallationPresenter {

	/**
	 * @var InfoManager IQRF GW Info manager
	 */
	private $infoManager;

	/**
	 * @var NetworkManager Network manager
	 */
	private $networkManager;

	/**
	 * @var VersionManager Version manager
	 */
	private $versionManager;

	/**
	 * Constructor
	 * @param InfoManager $infoManager IQRF GW Info manager
	 * @param NetworkManager $networkManager Network manager
	 * @param VersionManager $versionManager Version manager
	 */
	public function __construct(InfoManager $infoManager, NetworkManager $networkManager, VersionManager $versionManager) {
		$this->infoManager = $infoManager;
		$this->networkManager = $networkManager;
		$this->versionManager = $versionManager;
		parent::__construct();
	}

	/**
	 * Renders a default page
	 */
	public function renderDefault(): void {
		$this->template->ipAddresses = $this->networkManager->getIpAddresses();
		$this->template->macAddresses = $this->networkManager->getMacAddresses();
		$this->template->board = $this->infoManager->getBoard();
		$this->template->hostname = $this->networkManager->getHostname();
		$this->template->controllerVersion = $this->versionManager->getController();
		$this->template->daemonVersion = $this->versionManager->getDaemon(true);
		$this->template->webAppVersion = $this->versionManager->getWebapp(true);
		$this->template->gwId = $this->infoManager->getId();
		$this->template->gwmonId = $this->infoManager->getPixlaToken();
		try {
			$this->template->module = $this->infoManager->getCoordinatorInfo()['response']->data->rsp;
		} catch (DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->flashError('gateway.info.tr.error');
		}
	}

	/**
	 * Downloads gateway information as JSON
	 */
	public function actionDownload(): void {
		$data = [
			'board' => $this->infoManager->getBoard(),
			'gwId' => $this->infoManager->getId(),
			'pixla' => $this->infoManager->getPixlaToken(),
			'controllerVersion' => $this->versionManager->getController(),
			'daemonVersion' => $this->versionManager->getDaemon(true),
			'webappVersion' => $this->versionManager->getWebapp(true),
			'hostname' => $this->networkManager->getHostname(),
			'ipAddresses' => $this->networkManager->getIpAddresses(),
			'macAddresses' => $this->networkManager->getMacAddresses(),
		];
		try {
			$data['coordinator'] = $this->infoManager->getCoordinatorInfo();
		} catch (DpaErrorException | EmptyResponseException | JsonException $e) {
			$data['coordinator'] = 'ERROR';
		}
		$this->sendJson($data);
	}

}
