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
	public $baseServiceFactory;

	/**
	 * @var Forms\ConfigComponentsFormFactory
	 * @inject
	 */
	public $componentsFactory;

	/**
	 * @var Forms\ConfigIqrfAppFormFactory
	 * @inject
	 */
	public $iqrfAppFactory;

	/**
	 * @var Forms\ConfigIqrfFormFactory
	 * @inject
	 */
	public $iqrfFactory;

	/**
	 * @var Forms\ConfigMainFormFactory
	 * @inject
	 */
	public $mainFactory;

	/**
	 * @var Forms\ConfigMqFormFactory
	 * @inject
	 */
	public $mqFactory;

	/**
	 * @var Forms\ConfigMqttFormFactory
	 * @inject
	 */
	public $mqttFactory;

	/**
	 * @var Forms\ConfigSchedulerFormFactory
	 * @inject
	 */
	public $schedulerFactory;

	/**
	 * @var Forms\ConfigTracerFormFactory
	 * @inject
	 */
	public $tracerFactory;

	/**
	 * @var Forms\ConfigUdpFormFactory
	 * @inject
	 */
	public $udpFactory;

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
	 * @param InterfaceManager $interfaceManager
	 */
	public function __construct(ConfigManager $configManager, InterfaceManager $interfaceManager) {
		$this->configManager = $configManager;
		$this->interfaceManager = $interfaceManager;
	}

	/**
	 * Render default page
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
	}

	/**
	 * Render base service page
	 * @param int $id Id of base service
	 */
	public function renderBaseService($id = NULL) {
		$this->onlyForAdmins();
		$this->template->id = $id;
		if (!$id) {
			$this->template->services = $this->configManager->read('BaseService')['Instances'];
		}
	}

	/**
	 * Render IQRF page
	 */
	public function renderIqrf() {
		$this->onlyForAdmins();
		$this->template->interfaces = $this->interfaceManager->createInterfaceList();
	}

	/**
	 * Render MQTT page
	 * @param int $id ID of MQTT interface
	 */
	public function renderMqtt($id = NULL) {
		$this->onlyForAdmins();
		$this->template->id = $id;
		if (!$id) {
			$this->template->instances = $this->configManager->read('MqttMessaging')['Instances'];
		}
	}

	/**
	 * Render scheduler page
	 * @param int $id ID of task
	 */
	public function renderScheduler($id = NULL) {
		$this->onlyForAdmins();
		$this->template->id = $id;
		if (!$id) {
			$this->template->tasks = $this->configManager->read('Scheduler')['TasksJson'];
		}
	}

	/**
	 * Delete Base service
	 * @param int $id
	 */
	public function actionBaseServiceDelete($id) {
		$this->onlyForAdmins();
		$this->configManager->deleteBaseService($id);
		$this->redirect('Config:baseService', ['id' => null]);
		$this->setView('baseService');
	}

	/**
	 * Delete MQTT interface
	 * @param int $id
	 */
	public function actionMqttDelete($id) {
		$this->onlyForAdmins();
		$this->configManager->deleteInstances('MqttMessaging' ,$id);
		$this->redirect('Config:mqtt', ['id' => null]);
		$this->setView('mqtt');
	}

	/**
	 * Delete Scheduler task
	 * @param int $id
	 */
	public function actionSchedulerDelete($id) {
		$this->onlyForAdmins();
		$this->configManager->deleteScheduler($id);
		$this->redirect('Config:scheduler', ['id' => null]);
		$this->setView('scheduler');
	}

	/**
	 * Create Base service form
	 * @return Form Bse service form
	 */
	protected function createComponentConfigBaseServiceForm() {
		$this->onlyForAdmins();
		return $this->baseServiceFactory->create($this);
	}

	/**
	 * Create components form
	 * @return Form Components form
	 */
	protected function createComponentConfigComponentsForm() {
		$this->onlyForAdmins();
		return $this->componentsFactory->create($this);
	}

	/**
	 * Create IQRF app form
	 * @return Form IQRF app form
	 */
	protected function createComponentConfigIqrfAppForm() {
		$this->onlyForAdmins();
		return $this->iqrfAppFactory->create($this);
	}

	/**
	 * Create IQRF interface form
	 * @return Form IQRF interface form
	 */
	protected function createComponentConfigIqrfForm() {
		$this->onlyForAdmins();
		return $this->iqrfFactory->create($this);
	}

	/**
	 * Create Main daemon settings form
	 * @return Form Main daemon settings form
	 */
	protected function createComponentConfigMainForm() {
		$this->onlyForAdmins();
		return $this->mainFactory->create($this);
	}

	/**
	 * Create MQ interface form
	 * @return Form MQ interface form
	 */
	protected function createComponentConfigMqForm() {
		$this->onlyForAdmins();
		return $this->mqFactory->create($this);
	}

	/**
	 * Create MQTT interface form
	 * @return Form MQTT interface form
	 */
	protected function createComponentConfigMqttForm() {
		$this->onlyForAdmins();
		return $this->mqttFactory->create($this);
	}

	/**
	 * Create Scheduler form
	 * @return Form Scheduler form
	 */
	protected function createComponentConfigSchedulerForm() {
		$this->onlyForAdmins();
		return $this->schedulerFactory->create($this);
	}

	/**
	 * Create Tracer form
	 * @return Form Tracer form
	 */
	protected function createComponentConfigTracerForm() {
		$this->onlyForAdmins();
		return $this->tracerFactory->create($this);
	}

	/**
	 * Create UDP interface form
	 * @return Form UDP interface form
	 */
	protected function createComponentConfigUdpForm() {
		$this->onlyForAdmins();
		return $this->udpFactory->create($this);
	}

}
