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

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Model\GenericManager;
use App\ConfigModule\Forms\MqFormFactory;
use Nette\Forms\Form;

/**
 * MQ interface configuration presenter
 */
class MqPresenter extends GenericPresenter {

	/**
	 * @var MqFormFactory MQ inteface configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * Constructor
	 * @param GenericManager $genericManager Generic configuration manager
	 */
	public function __construct(GenericManager $genericManager) {
		$components = ['iqrf::MqMessaging'];
		parent::__construct($components, $genericManager);
	}

	/**
	 * Render list of MQ interfaces
	 */
	public function renderDefault() {
		$this->template->instances = $this->configManager->getInstances();
	}

	/**
	 * Edit MQ interface
	 * @param int $id ID of MQ interface
	 */
	public function renderEdit(int $id) {
		$this->template->id = $id;
	}

	/**
	 * Delete MQ interface
	 * @param int $id ID of MQ interface
	 */
	public function actionDelete(int $id) {
		$fileName = $this->configManager->getInstanceFiles()[$id];
		$this->configManager->setFileName($fileName);
		$this->configManager->delete();
		$this->redirect('Mq:default');
		$this->setView('default');
	}

	/**
	 * Create MQ interface configuration form
	 * @return Form MQ interface configuration form
	 */
	protected function createComponentConfigMqForm(): Form {
		return $this->formFactory->create($this);
	}

}
