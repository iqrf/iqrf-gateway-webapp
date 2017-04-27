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
use App\Forms;

/**
 * Configuration presenter
 */
class ConfigPresenter extends BasePresenter {

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
	 * @persistent
	 * @var int
	 */
	public $id;

	/**
	 * @param ConfigManager $configManager
	 */
	public function __construct(ConfigManager $configManager) {
		$this->configManager = $configManager;
	}

	public function renderDefault() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}

	public function renderMqtt($id) {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		$this->template->id = $id;
		$this->id = $id;
		if (!$id) {
			$this->template->instances = $this->configManager->read('MqttMessaging')['Instances'];
		}
	}

	/**
	 * Create components form
	 * @return Form Components form
	 */
	protected function createComponentConfigComponentsForm() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		return $this->configComponentsFactory->create($this);
	}

	/**
	 * Create IQRF app form
	 * @return Form IQRF app form
	 */
	protected function createComponentConfigIqrfAppForm() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		return $this->configIqrfAppFactory->create($this);
	}

	/**
	 * Create IQRF interface form
	 * @return Form IQRF interface form
	 */
	protected function createComponentConfigIqrfForm() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		return $this->configIqrfFactory->create($this);
	}

	/**
	 * Create MQ interface form
	 * @return Form MQ interface form
	 */
	protected function createComponentConfigMqForm() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		return $this->configMqFactory->create($this);
	}

	/**
	 * Create MQTT interface form
	 * @return Form MQTT interface form
	 */
	protected function createComponentConfigMqttForm() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		return $this->configMqttFactory->create($this);
	}

	/**
	 * Create Tracer form
	 * @return Form Tracer form
	 */
	protected function createComponentConfigTracerForm() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		return $this->configTracerFactory->create($this);
	}

	/**
	 * Create UDP interface form
	 * @return Form UDP interface form
	 */
	protected function createComponentConfigUdpForm() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		return $this->configUdpFactory->create($this);
	}

}
