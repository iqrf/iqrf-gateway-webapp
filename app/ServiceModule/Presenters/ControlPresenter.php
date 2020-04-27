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

namespace App\ServiceModule\Presenters;

use App\CoreModule\Presenters\ProtectedPresenter;
use App\CoreModule\Traits\TPresenterFlashMessage;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;

/**
 * Service control presenter.
 */
class ControlPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

	/**
	 * @var ServiceManager Service manager
	 */
	private $serviceManager;

	/**
	 * Constructor
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(ServiceManager $serviceManager) {
		$this->serviceManager = $serviceManager;
		parent::__construct();
	}

	/**
	 * Starts, stops or restarts IQRF Gateway Daemon's service
	 * @param string $action Type of action (start/stop/restart)
	 */
	private function action(string $action): void {
		try {
			switch ($action) {
				case 'start':
					$this->serviceManager->start();
					break;
				case 'stop':
					$this->serviceManager->stop();
					break;
				case 'restart':
					$this->serviceManager->restart();
					break;
			}
			$this->flashSuccess('service.actions.' . $action . '.message');
		} catch (UnsupportedInitSystemException $ex) {
			$this->flashError('service.errors.unsupportedInit');
		}
		$this->readStatus();
	}

	/**
	 * Starts IQRF Gateway Daemon's service
	 */
	public function handleStart(): void {
		$this->action('start');
	}

	/**
	 * Stops IQRF Gateway Daemon's service
	 */
	public function handleStop(): void {
		$this->action('stop');
	}

	/**
	 * Restarts IQRF Gateway Daemon's service
	 */
	public function handleRestart(): void {
		$this->action('restart');
	}

	/**
	 * Refreshes IQRF Gateway Daemon's service status
	 */
	public function handleStatus(): void {
		$this->readStatus();
	}

	/**
	 * Reads IQRF Gateway Daemon's service status
	 */
	private function readStatus(): void {
		try {
			$this->template->status = $this->serviceManager->getStatus();
			$this->redrawControl('status');
		} catch (UnsupportedInitSystemException $ex) {
			$this->flashError('gateway.errors.unsupportedInit');
		}
	}

	/**
	 * Renders IQRF Gateway Daemon's service status
	 */
	public function renderDefault(): void {
		if (!$this->isAjax()) {
			$this->readStatus();
		}
	}

}
