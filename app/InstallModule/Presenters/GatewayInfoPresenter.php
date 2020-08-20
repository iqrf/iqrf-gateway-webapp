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
	 * Constructor
	 * @param InfoManager $infoManager IQRF GW Info manager
	 */
	public function __construct(InfoManager $infoManager) {
		$this->infoManager = $infoManager;
		parent::__construct();
	}

	/**
	 * Renders a default page
	 */
	public function renderDefault(): void {
		$this->template->info = $this->infoManager->get(true);
		try {
			$this->template->coordinatorInfo = $this->infoManager->getCoordinatorInfo();
		} catch (DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->flashError('gateway.info.tr.error');
		}
	}

	/**
	 * Downloads gateway information as JSON
	 */
	public function actionDownload(): void {
		$json = $this->infoManager->get(true);
		try {
			$json['coordinator'] = $this->infoManager->getCoordinatorInfo();
		} catch (DpaErrorException | EmptyResponseException | JsonException $e) {
			$json['coordinator'] = null;
		}
		$this->sendJson($json);
	}

}
