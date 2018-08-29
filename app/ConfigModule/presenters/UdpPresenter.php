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

use App\ConfigModule\Forms\UdpFormFactory;
use App\ConfigModule\Model\GenericManager;
use Nette\Forms\Form;

/**
 * UDP interface configuration presenter
 */
class UdpPresenter extends GenericPresenter {

	/**
	 * @var UdpFormFactory UDP interface configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * Constructor
	 * @param GenericManager $genericManager Generic configuration manager
	 */
	public function __construct(GenericManager $genericManager) {
		$components = ['iqrf::UdpMessaging'];
		parent::__construct($components, $genericManager);
	}

	/**
	 * Render list of UDP interfaces
	 */
	public function renderDefault(): void {
		$this->template->instances = $this->configManager->getInstances();
	}

	/**
	 * Edit UDP interface
	 * @param int $id ID of UDP interface
	 */
	public function renderEdit(int $id): void {
		$this->template->id = $id;
	}

	/**
	 * Delete UDP interface
	 * @param int $id ID of UDP interface
	 */
	public function actionDelete(int $id): void {
		$fileName = $this->configManager->getInstanceFiles()[$id];
		$this->configManager->setFileName($fileName);
		$this->configManager->delete();
		$this->redirect('Udp:default');
		$this->setView('default');
	}

	/**
	 * Create UDP interface configuration form
	 * @return Form UDP interface configuration form
	 */
	protected function createComponentConfigUdpForm(): Form {
		return $this->formFactory->create($this);
	}

}
