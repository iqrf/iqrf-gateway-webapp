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

use App\ConfigModule\Forms\IqrfSpiFormFactory;
use App\ConfigModule\Model\GenericManager;
use App\ConfigModule\Model\IqrfManager;
use App\Model\JsonFileManager;
use Nette\Forms\Form;

/**
 * IQRF SPI interface configuration presenter
 */
class IqrfSpiPresenter extends GenericPresenter {

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var IqrfSpiFormFactory IQRF SPI interface configuration form factory
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
	 * @param GenericManager $genericManager Generic configuration manager
	 */
	public function __construct(IqrfManager $iqrfManager, GenericManager $genericManager) {
		$this->iqrfManager = $iqrfManager;
		$this->fileManager = new JsonFileManager(__DIR__ . '/../json/');
		$components = ['iqrf::IqrfCdc'];
		parent::__construct($components, $genericManager);
	}

	/**
	 * Render IQRF SPI interface configurator
	 */
	public function renderDefault() {
		$this->template->spiInterfaces = $this->iqrfManager->getSpiInterfaces();
		$this->template->spiPins = $this->fileManager->read('SpiPins');
	}

	/**
	 * Create IQRF SPI interface configuration form
	 * @return Form IQRF SPI interface configuration form
	 */
	protected function createComponentConfigIqrfSpiForm(): Form {
		return $this->spiFormFactory->create($this);
	}

}
