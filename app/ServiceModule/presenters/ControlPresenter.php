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
use App\ServiceModule\Exceptions\NotSupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;

/**
 * Service control presenter.
 */
class ControlPresenter extends ProtectedPresenter {

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
	 * Starts IQRF Gateway Daemon's service
	 */
	public function actionStart(): void {
		$this->action('start');
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
			$this->flashMessage('service.actions.' . $action . '.message', 'success');
		} catch (NotSupportedInitSystemException $ex) {
			$this->flashMessage('service.errors.unsupportedInit', 'danger');
		} finally {
			$this->redirect('Control:default');
			$this->setView('default');
		}
	}

	/**
	 * Stops IQRF Gateway Daemon's service
	 */
	public function actionStop(): void {
		$this->action('stop');
	}

	/**
	 * Restarts IQRF Gateway Daemon's service
	 */
	public function actionRestart(): void {
		$this->action('restart');
	}

}
