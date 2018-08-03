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

namespace App\ServiceModule\Presenters;

use App\Presenters\ProtectedPresenter;
use App\ServiceModule\Model\NotSupportedInitSystemException;
use App\ServiceModule\Model\ServiceManager;

/**
 * Service status presenter
 */
class StatusPresenter extends ProtectedPresenter {

	/**
	 * @var ServiceManager Service manager
	 */
	private $serviceManager;

	/**
	 * Constructor.
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(ServiceManager $serviceManager) {
		$this->serviceManager = $serviceManager;
		parent::__construct();
	}

	/**
	 * Render IQRF Gateway Daemon's service status
	 */
	public function renderDefault() {
		try {
			$status = $this->serviceManager->getStatus();
			$this->template->status = $status;
		} catch (NotSupportedInitSystemException $ex) {
			$this->flashMessage('gateway.errors.unsupportedInit', 'danger');
			$this->redirect('Control:default');
			$this->setView('default');
		}
	}

}
