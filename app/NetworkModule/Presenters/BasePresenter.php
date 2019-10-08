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

namespace App\NetworkModule\Presenters;

use App\CoreModule\Models\CommandManager;
use App\CoreModule\Presenters\ProtectedPresenter;

/**
 * Base presenter for network module
 */
class BasePresenter extends ProtectedPresenter {

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
		parent::__construct();
	}

	/**
	 * Checks requirements
	 * @param mixed $element Element
	 */
	public function checkRequirements($element): void {
		parent::checkRequirements($element);
		if (!$this->context->parameters['features']['networkManager']) {
			$this->flashError('network.messages.disabled');
			$this->redirect(':Core:Homepage:default');
		}
		if (!$this->commandManager->commandExist('nmcli')) {
			$this->flashError('network.messages.missingNetworkManager');
			$this->redirect(':Core:Homepage:default');
		}
	}

}
