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

namespace App\ConfigModule\Presenters;

use App\Forms\ConfigMqttFormFactory;
use App\Presenters\BasePresenter;
use App\Model\ConfigManager;

class MqttPresenter extends BasePresenter {

	/**
	 * @var ConfigMqttFormFactory
	 */
	private $formFactory;
	
	/**
	 * @var ConfigManager
	 */
	private $configManager;
	
	/**
	 * Constructor
	 * @param ConfigMqttFormFactory $formFactory
	 * @param ConfigManager $configManager
	 */
	public function __construct(ConfigMqttFormFactory $formFactory, ConfigManager $configManager) {
		$this->configManager = $configManager;
		$this->formFactory = $formFactory;
	}
	
	/**
	 * Render list of MQTT interfaces
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
		$this->template->instances = $this->configManager->read('MqttMessaging')['Instances'];
	}
	
	/**
	 * Edit MQTT interface
	 * @param int $id ID of MQTT interface
	 */
	public function renderEdit($id) {
		$this->onlyForAdmins();
		$this->template->id = $id;
	}

	/**
	 * Delete MQTT interface
	 * @param int $id ID of MQTT interface
	 */
	public function actionDelete($id) {
		$this->onlyForAdmins();
		$this->configManager->deleteInstances('MqttMessaging' ,$id);
		$this->redirect('Mqtt:default');
		$this->setView('default');
	}

	/**
	 * Create MQTT interface form
	 * @return Form MQTT interface form
	 */
	protected function createComponentConfigMqttForm() {
		$this->onlyForAdmins();
		return $this->formFactory->create($this);
	}

}
