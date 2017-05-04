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

use App\Model\ServiceManager;

/**
 * Service presenter
 */
class ServicePresenter extends BasePresenter {

	/**
	 * @var ServiceManager
	 * @inject
	 */
	private $serviceManager;

	/**
	 * @param ServiceManager $serviceManager
	 */
	public function __construct(ServiceManager $serviceManager) {
		$this->serviceManager = $serviceManager;
	}

	public function renderDefault() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}
	
	public function actionStart() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		$this->serviceManager->start();
		$this->flashMessage('IQRF Daemon has been started.', 'info');
		$this->redirect('Service:default');
		$this->setView('default');
	}
	
	public function actionStop() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		$this->serviceManager->stop();
		$this->flashMessage('IQRF Daemon has been stopped.', 'info');
		$this->redirect('Service:default');
		$this->setView('default');
	}
	
	public function actionRestart() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		$this->serviceManager->restart();
		$this->flashMessage('IQRF Daemon has been restarted.', 'info');
		$this->redirect('Service:default');
		$this->setView('default');
	}

	
	public function renderStatus() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		$status = $this->serviceManager->getStatus();
		$this->template->status = $status;
	}
}
