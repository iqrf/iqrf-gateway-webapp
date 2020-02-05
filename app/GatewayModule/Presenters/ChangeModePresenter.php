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
use App\CoreModule\Traits\TPresenterFlashMessage;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\InvalidOperationModeException;
use App\IqrfNetModule\Models\GwModeManager;
use Nette\Utils\JsonException;

/**
 * Change IQRF Gateway Daemon's gateway mode presenter
 */
class ChangeModePresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

	/**
	 * @var GwModeManager IQRF Gateway Daemon's mode manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param GwModeManager $manager IQRF Gateway Daemon's mode manager
	 */
	public function __construct(GwModeManager $manager) {
		$this->manager = $manager;
		parent::__construct();
	}

	/**
	 * Changes IQRF Gateway Daemon's mode
	 * @param string $mode New IQRF Gateway's mode
	 * @throws EmptyResponseException
	 * @throws InvalidOperationModeException
	 * @throws JsonException
	 */
	private function changeMode(string $mode): void {
		$this->setView('default');
		try {
			$this->manager->set($mode);
			$this->flashSuccess('gateway.mode.modes.' . $mode . '.message');
		} catch (EmptyResponseException | DpaErrorException $e) {
			$this->flashError('iqrfnet.webSocketClient.messages.emptyResponse');
		}
	}

	/**
	 * Changes IQRF Gateway's mode to Forwarding mode
	 * @throws EmptyResponseException
	 * @throws InvalidOperationModeException
	 * @throws JsonException
	 */
	public function handleForwarding(): void {
		$this->changeMode('forwarding');
	}

	/**
	 * Changes IQRF Gateway's mode to Operational mode
	 * @throws EmptyResponseException
	 * @throws InvalidOperationModeException
	 * @throws JsonException
	 */
	public function handleOperational(): void {
		$this->changeMode('operational');
	}

	/**
	 * Changes gateway mode to Service mode
	 * @throws EmptyResponseException
	 * @throws InvalidOperationModeException
	 * @throws JsonException
	 */
	public function handleService(): void {
		$this->changeMode('service');
	}

	/**
	 * Renders gateway mode control panel
	 */
	public function renderDefault(): void {
		try {
			$this->template->gwMode = $this->manager->get();
			$this->redrawControl('mode');
		} catch (DpaErrorException | EmptyResponseException $e) {
			$this->template->gwMode = 'unknown';
		}
	}

}
