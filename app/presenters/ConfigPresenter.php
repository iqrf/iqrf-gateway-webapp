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

use App\Model\ConfigManager;
use App\Model\InterfaceManager;
use App\Forms;

/**
 * Configuration presenter
 */
class ConfigPresenter extends BasePresenter {

	/**
	 * @var Forms\ConfigBaseServiceFormFactory
	 * @inject
	 */
	public $configBaseServiceFactory;

	/**
	 * @var Forms\ConfigComponentsFormFactory
	 * @inject
	 */
	public $configComponentsFactory;

	/**
	 * @var Forms\ConfigIqrfAppFormFactory
	 * @inject
	 */
	public $configIqrfAppFactory;

	/**
	 * @var Forms\ConfigIqrfFormFactory
	 * @inject
	 */
	public $configIqrfFactory;

	/**
	 * @var Forms\ConfigMqFormFactory
	 * @inject
	 */
	public $configMqFactory;

	/**
	 * @var Forms\ConfigMqttFormFactory
	 * @inject
	 */
	public $configMqttFactory;

	/**
	 * @var Forms\ConfigSchedulerFormFactory
	 * @inject
	 */
	public $configSchedulerFactory;

	/**
	 * @var Forms\ConfigTracerFormFactory
	 * @inject
	 */
	public $configTracerFactory;

	/**
	 * @var Forms\ConfigUdpFormFactory
	 * @inject
	 */
	public $configUdpFactory;

	/**
	 * @var ConfigManager
	 * @inject
	 */
	private $configManager;

	/**
	 * @var InterfaceManager
	 * @inject
	 */
	private $interfaceManager;

	/**
	 * Constructor
	 * @param ConfigManager $configManager
	 */
	public function __construct(ConfigManager $configManager, InterfaceManager $interfaceManager) {
		$this->configManager = $configManager;
		$this->interfaceManager = $interfaceManager;
	}

	public function renderDefault() {
		$this->onlyForAdmins();
	}

	public function renderBaseService($id = NULL) {
		$this->onlyForAdmins();
		$this->template->id = $id;
		if (!$id) {
			$this->template->services = $this->configManager->read('BaseService')['Instances'];
		}
	}

	public function renderIqrf() {
		$this->onlyForAdmins();
		$this->template->interfaces = $this->interfaceManager->createInterfaceList();
	}

	public function renderMqtt($id = NULL) {
		$this->onlyForAdmins();
		$this->template->id = $id;
		if (!$id) {
			$this->template->instances = $this->configManager->read('MqttMessaging')['Instances'];
		}
	}

	public function renderScheduler($id = NULL) {
		$this->onlyForAdmins();
		$this->template->id = $id;
		if (!$id) {
			$this->template->tasks = $this->configManager->read('Scheduler')['TasksJson'];
		}
	}

	/**
	 * Create Base service form
	 * @return Form Bse service form
	 */
	protected function createComponentConfigBaseServiceForm() {
		$this->onlyForAdmins();
		return $this->configBaseServiceFactory->create($this);
	}

	/**
	 * Create components form
	 * @return Form Components form
	 */
	protected function createComponentConfigComponentsForm() {
		$this->onlyForAdmins();
		return $this->configComponentsFactory->create($this);
	}

	/**
	 * Create IQRF app form
	 * @return Form IQRF app form
	 */
	protected function createComponentConfigIqrfAppForm() {
		$this->onlyForAdmins();
		return $this->configIqrfAppFactory->create($this);
	}

	/**
	 * Create IQRF interface form
	 * @return Form IQRF interface form
	 */
	protected function createComponentConfigIqrfForm() {
		$this->onlyForAdmins();
		return $this->configIqrfFactory->create($this);
	}

	/**
	 * Create MQ interface form
	 * @return Form MQ interface form
	 */
	protected function createComponentConfigMqForm() {
		$this->onlyForAdmins();
		return $this->configMqFactory->create($this);
	}

	/**
	 * Create MQTT interface form
	 * @return Form MQTT interface form
	 */
	protected function createComponentConfigMqttForm() {
		$this->onlyForAdmins();
		return $this->configMqttFactory->create($this);
	}

	/**
	 * Create Scheduler form
	 * @return Form Scheduler form
	 */
	protected function createComponentConfigSchedulerForm() {
		$this->onlyForAdmins();
		return $this->configSchedulerFactory->create($this);
	}

	/**
	 * Create Tracer form
	 * @return Form Tracer form
	 */
	protected function createComponentConfigTracerForm() {
		$this->onlyForAdmins();
		return $this->configTracerFactory->create($this);
	}

	/**
	 * Create UDP interface form
	 * @return Form UDP interface form
	 */
	protected function createComponentConfigUdpForm() {
		$this->onlyForAdmins();
		return $this->configUdpFactory->create($this);
	}

}
