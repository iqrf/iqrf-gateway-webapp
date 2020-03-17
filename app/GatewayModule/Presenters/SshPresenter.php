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
use App\GatewayModule\Models\SshManager;

/**
 * SSH daemon manager presenter
 */
class SshPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

	/**
	 * @var SshManager SSH daemon manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param SshManager $manager SSH daemon manager
	 */
	public function __construct(SshManager $manager) {
		$this->manager = $manager;
		parent::__construct();
	}

	/**
	 * Disables and stops SSH daemon
	 */
	public function handleDisable(): void {
		$this->manager->disableService();
		$this->flashSuccess('gateway.unattendedUpgrades.messages.disable');
	}

	/**
	 * Enables and starts SSH daemon
	 */
	public function handleEnable(): void {
		$this->manager->enableService();
		$this->flashSuccess('gateway.unattendedUpgrades.messages.enable');
	}

	/**
	 * Renders SSH daemon control panel
	 */
	public function renderDefault(): void {
		$this->template->status = $this->manager->getServiceStatus();
		$this->redrawControl('status');
	}

	/**
	 * Checks if the SSH daemon is enabled
	 */
	protected function startup(): void {
		parent::startup();
		if (!$this->context->parameters['features']['ssh']) {
			$this->flashError('gateway.ssh.messages.disabled');
			$this->redirect('Homepage:default');
		}
	}

}
