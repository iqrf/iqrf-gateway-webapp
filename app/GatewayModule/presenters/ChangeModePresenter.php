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

declare(strict_types=1);

namespace App\GatewayModule\Presenters;

use App\IqrfAppModule\Model\IqrfAppManager;
use App\Presenters\BasePresenter;

/**
 * Change operational mode presenter
 */
class ChangeModePresenter extends BasePresenter {

	/**
	 * @var IqrfAppManager
	 */
	private $iqrfAppManager;

	/**
	 * Constructor
	 * @param IqrfAppManager $iqrfAppManager
	 */
	public function __construct(IqrfAppManager $iqrfAppManager) {
		$this->iqrfAppManager = $iqrfAppManager;
	}

	/**
	 * Render default page
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
	}

	/**
	 * Change gateway mode to Forwarding mode
	 */
	public function actionForwarding() {
		$this->onlyForAdmins();
		$mode = 'forwarding';
		$this->iqrfAppManager->changeOperationMode($mode);
		$this->flashMessage('gateway.mode.modes.' . $mode . '.message', 'info');
		$this->redirect('ChangeMode:default');
		$this->setView('default');
	}

	/**
	 * Change gateway mode to Operational mode
	 */
	public function actionOperational() {
		$this->onlyForAdmins();
		$mode = 'operational';
		$this->iqrfAppManager->changeOperationMode($mode);
		$this->flashMessage('gateway.mode.modes.' . $mode . '.message', 'info');
		$this->redirect('ChangeMode:default');
		$this->setView('default');
	}

	/**
	 * Change gateway mode to Service mode
	 */
	public function actionService() {
		$this->onlyForAdmins();
		$mode = 'service';
		$this->iqrfAppManager->changeOperationMode($mode);
		$this->flashMessage('gateway.mode.modes.' . $mode . '.message', 'info');
		$this->redirect('ChangeMode:default');
		$this->setView('default');
	}

}
