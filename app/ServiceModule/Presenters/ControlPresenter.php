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
use App\ServiceModule\Exceptions\NonexistentServiceException;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use Nette\Application\BadRequestException;

/**
 * Service control presenter.
 */
class ControlPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

	/**
	 * Whitelisted services
	 */
	private const WHITELISTED = ['iqrf-gateway-daemon', 'ssh', 'unattended-upgrades'];

	/**
	 * @var ServiceManager Service manager
	 */
	protected $manager;

	/**
	 * Constructor
	 * @param ServiceManager $manager Service manager
	 */
	public function __construct(ServiceManager $manager) {
		$this->manager = $manager;
		parent::__construct();
	}

	/**
	 * Disables, enables, starts, stops or restarts service
	 * @param string $action Type of action
	 * @var string $name Service name
	 */
	private function action(string $action, string $name): void {
		try {
			switch ($action) {
				case 'disable':
					$this->manager->disable($name);
					break;
				case 'enable':
					$this->manager->enable($name);
					break;
				case 'start':
					$this->manager->start($name);
					break;
				case 'stop':
					$this->manager->stop($name);
					break;
				case 'restart':
					$this->manager->restart($name);
					break;
			}
			$this->flashSuccess('service.' . $name . '.messages.' . $action);
		} catch (UnsupportedInitSystemException $ex) {
			$this->flashError('service.errors.unsupportedInit');
		}
		$this->readStatus($name);
	}

	/**
	 * Disables service
	 * @var string $name Service name
	 */
	public function handleDisable(string $name): void {
		$this->action('disable', $name);
	}

	/**
	 * Enables service
	 * @var string $name Service name
	 */
	public function handleEnable(string $name): void {
		$this->action('enable', $name);
	}

	/**
	 * Starts service
	 * @var string $name Service name
	 */
	public function handleStart(string $name): void {
		$this->action('start', $name);
	}

	/**
	 * Stops service
	 * @var string $name Service name
	 */
	public function handleStop(string $name): void {
		$this->action('stop', $name);
	}

	/**
	 * Restarts service
	 * @var string $name Service name
	 */
	public function handleRestart(string $name): void {
		$this->action('restart', $name);
	}

	/**
	 * Refreshes service status
	 * @var string $name Service name
	 */
	public function handleStatus(string $name): void {
		$this->readStatus($name);
	}

	/**
	 * Reads service status
	 * @var string $name Service name
	 */
	private function readStatus(string $name): void {
		try {
			$this->template->active = $this->manager->isActive($name);
			$this->template->enabled = $this->manager->isEnabled($name);
			$this->template->status = $this->manager->getStatus($name);
		} catch (NonexistentServiceException $e) {
			$this->template->active = null;
			$this->template->enabled = null;
			$this->template->status = null;
		} catch (UnsupportedInitSystemException $ex) {
			$this->flashError('gateway.errors.unsupportedInit');
		}
		$this->redrawControl('status');
	}

	/**
	 * Renders service status
	 * @var string $name Service name
	 */
	public function renderDefault(string $name): void {
		$this->template->service = $name;
		if (!$this->isAjax()) {
			$this->readStatus($name);
		}
	}

	/**
	 * Checks if the service is whitelisted
	 * @param string $name Service name
	 * @throws BadRequestException
	 */
	public function actionDefault(string $name): void {
		if (!in_array($name, self::WHITELISTED, true)) {
			throw new BadRequestException('Unsupported service ' . $name);
		}
		if ($name === 'unattended-upgrades' && !$this->context->parameters['features']['unattendedUpgrades'] ||
			$name === 'ssh' && !$this->context->parameters['features']['ssh']) {
			$this->flashError('service.' . $name . '.messages.disabled');
			$this->redirect(':Gateway:Homepage:default');
		}
	}

}
