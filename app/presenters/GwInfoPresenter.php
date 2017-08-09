<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
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

namespace App\Presenters;

use App\Model\GwInfoManager;
use App\Model\IqrfAppManager;
use App\Model\IqrfAppParser;

/**
 * Gateway Info presenter
 */
class GwInfoPresenter extends BasePresenter {

	/**
	 * @var GwInfoManager
	 */
	private $gwInfoManager;

	/**
	 * @var IqrfAppManager
	 */
	private $iqrfAppManager;

	/**
	 * @var IqrfAppParser
	 */
	private $iqrfAppParser;

	/**
	 * Constructor
	 * @param GwInfoManager $gwInfoManager
	 * @param IqrfAppManager $iqrfAppManager
	 */
	public function __construct(GwInfoManager $gwInfoManager, IqrfAppManager $iqrfAppManager, IqrfAppParser $iqrfAppParser) {
		$this->gwInfoManager = $gwInfoManager;
		$this->iqrfAppManager = $iqrfAppManager;
		$this->iqrfAppParser = $iqrfAppParser;
	}

	/**
	 * Render default page
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
		$this->template->ipAddresses = $this->gwInfoManager->getIpAddresses();
		$this->template->macAddresses = $this->gwInfoManager->getMacAddresses();
		$this->template->hostname = $this->gwInfoManager->getHostname();
		$response = $this->iqrfAppManager->sendRaw('00.00.02.00.FF.FF');
		$this->template->module = $this->iqrfAppParser->parseResponse($response);
	}

}
