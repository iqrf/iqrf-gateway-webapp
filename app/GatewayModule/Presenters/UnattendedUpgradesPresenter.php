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
use App\ServiceModule\Exceptions\NonexistentServiceException;
use App\ServiceModule\Models\ServiceManager;

/**
 * Unattended upgrades presenter
 */
class UnattendedUpgradesPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

	/**
	 * @var ServiceManager Service manager
	 */
	private $manager;

	/**
	 * Service name
	 */
	private const SERVICE_NAME = 'unattended-upgrades';

	/**
	 * Constructor
	 * @param ServiceManager $manager Service manager
	 */
	public function __construct(ServiceManager $manager) {
		$this->manager = $manager;
		parent::__construct();
	}

	/**
	 * Disables and stops unattended upgrades
	 */
	public function handleDisable(): void {
		$this->manager->disable(self::SERVICE_NAME);
		$this->flashSuccess('gateway.unattendedUpgrades.messages.disable');
	}

	/**
	 * Enables and starts unattended upgrades
	 */
	public function handleEnable(): void {
		$this->manager->enable(self::SERVICE_NAME);
		$this->flashSuccess('gateway.unattendedUpgrades.messages.enable');
	}

	/**
	 * Renders unattended upgrade control panel
	 */
	public function renderDefault(): void {
		try {
			$this->template->status = $this->manager->isEnabled(self::SERVICE_NAME) ? 'enabled' : 'disabled';
		} catch (NonexistentServiceException $e) {
			$this->template->status = 'missing';
		}
		$this->redrawControl('status');
	}

	/**
	 * Checks if the unattended upgrades is enabled
	 */
	protected function startup(): void {
		parent::startup();
		if (!$this->context->parameters['features']['unattendedUpgrades']) {
			$this->flashError('gateway.unattendedUpgrades.messages.disabled');
			$this->redirect('Homepage:default');
		}
	}

}
