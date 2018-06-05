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
declare(strict_types=1);

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Forms\ConfigIqrfFormFactory;
use App\ConfigModule\Forms\ConfigIqrfSpiFormFactory;
use App\ConfigModule\Model\IqrfManager;
use App\Presenters\BasePresenter;
use App\Model\JsonFileManager;

class IqrfPresenter extends BasePresenter {

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var ConfigIqrfFormFactory IQRF interface configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var ConfigIqrfSpiFormFactory IQRF SPI configuration form factory
	 * @inject
	 */
	public $spiFormFactory;

	/**
	 * @var IqrfManager IQRF interface manager
	 */
	private $iqrfManager;

	/**
	 * Constructor
	 * @param IqrfManager $iqrfManager IQRF interface manager
	 */
	public function __construct(IqrfManager $iqrfManager) {
		$this->iqrfManager = $iqrfManager;
		$this->fileManager = new JsonFileManager(__DIR__ . '/../json/');
	}

	/**
	 * Render IQRF interface configurator
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
		$this->template->interfaces = $this->iqrfManager->getInterfaces();
		$this->template->spiPins = $this->fileManager->read('SpiPins');
	}

	/**
	 * Create IQRF interface form
	 * @return Form IQRF interface form
	 */
	protected function createComponentConfigIqrfForm() {
		$this->onlyForAdmins();
		return $this->formFactory->create($this);
	}

	/**
	 * Create IQRF SPI interface form
	 * @return Form IQRF SPI interface form
	 */
	protected function createComponentConfigIqrfSpiForm() {
		$this->onlyForAdmins();
		return $this->spiFormFactory->create($this);
	}

}
