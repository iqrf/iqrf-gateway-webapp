<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
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

namespace App\ServiceModule\Presenters;

use App\Presenters\BasePresenter;
use App\ServiceModule\Model\NotSupportedInitSystemException;
use App\ServiceModule\Model\ServiceManager;

/**
 * Service control presenter.
 */
class ControlPresenter extends BasePresenter {

	/**
	 * @var ServiceManager
	 */
	private $serviceManager;

	/**
	 * Constructor.
	 * @param ServiceManager $serviceManager
	 */
	public function __construct(ServiceManager $serviceManager) {
		$this->serviceManager = $serviceManager;
	}

	/**
	 * Render disambiguation
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
	}

	/**
	 * Start iqrf-daemon service
	 */
	public function actionStart() {
		$this->onlyForAdmins();
		try {
			$this->serviceManager->start();
			$this->flashMessage('IQRF Daemon has been started.', 'info');
		} catch (NotSupportedInitSystemException $ex) {
			$this->flashMessage('Not supported init system is used.', 'danger');
		} finally {
			$this->redirect('Control:default');
			$this->setView('default');
		}
	}

	/**
	 * Stop iqrf-daemon service
	 */
	public function actionStop() {
		$this->onlyForAdmins();
		try {
			$this->serviceManager->stop();
			$this->flashMessage('IQRF Daemon has been stopped.', 'info');
		} catch (NotSupportedInitSystemException $ex) {
			$this->flashMessage('Not supported init system is used.', 'danger');
		} finally {
			$this->redirect('Control:default');
			$this->setView('default');
		}
	}

	/**
	 * Restart iqrf-daemon service
	 */
	public function actionRestart() {
		$this->onlyForAdmins();
		try {
			$this->serviceManager->restart();
			$this->flashMessage('IQRF Daemon has been restarted.', 'info');
		} catch (NotSupportedInitSystemException $ex) {
			$this->flashMessage('Not supported init system is used.', 'danger');
		} finally {
			$this->redirect('Control:default');
			$this->setView('default');
		}
	}

}
