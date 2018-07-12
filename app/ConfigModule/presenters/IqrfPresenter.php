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

use App\ConfigModule\Forms\ConfigIqrfCdcFormFactory;
use App\ConfigModule\Forms\ConfigIqrfDpaFormFactory;
use App\ConfigModule\Forms\ConfigIqrfIde4FormFactory;
use App\ConfigModule\Forms\ConfigIqrfSpiFormFactory;
use App\ConfigModule\Forms\ConfigOtaUploadFormFactory;
use App\ConfigModule\Model\IqrfManager;
use App\Presenters\ProtectedPresenter;
use App\Model\JsonFileManager;
use Nette\Forms\Form;

class IqrfPresenter extends ProtectedPresenter {

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var ConfigIqrfCdcFormFactory IQRF CDC interface configuration form factory
	 * @inject
	 */
	public $cdcFormFactory;

	/**
	 * @var ConfigIqrfDpaFormFactory IQRF DPA interface configuration form factory
	 * @inject
	 */
	public $dpaFormFactory;

	/**
	 * @var ConfigIqrfIde4FormFactory IQRF IDE 4 counterpart form factory
	 * @inject
	 */
	public $ide4FormFactory;

	/**
	 * @var ConfigOtaUploadFormFactory IQRF OTA upload service configuration form factory
	 * @inject
	 */
	public $otaFormFactory;

	/**
	 * @var ConfigIqrfSpiFormFactory IQRF SPI interface configuration form factory
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
		parent::__construct();
	}

	/**
	 * Render IQRF interface configurator
	 */
	public function renderDefault() {
		$this->template->cdcInterfaces = $this->iqrfManager->getCdcInterfaces();
		$this->template->spiInterfaces = $this->iqrfManager->getSpiInterfaces();
		$this->template->spiPins = $this->fileManager->read('SpiPins');
	}

	/**
	 * Create IQRF CDC interface form
	 * @return Form IQRF CDC interface form
	 */
	protected function createComponentConfigIqrfCdcForm() {
		return $this->cdcFormFactory->create($this);
	}

	/**
	 * Create IQRF DPA interface form
	 * @return Form IQRF DPA interface form
	 */
	protected function createComponentConfigIqrfDpaForm(): Form {
		return $this->dpaFormFactory->create($this);
	}

	/**
	 * Create IQRF IDE 4 counterpart form
	 * @return Form IQRF IDE 4 counterpart form
	 */
	protected function createComponentConfigIqrfIde4Form(): Form {
		return $this->ide4FormFactory->create($this);
	}

	/**
	 * Create IQRF SPI interface form
	 * @return Form IQRF SPI interface form
	 */
	protected function createComponentConfigIqrfSpiForm(): Form {
		return $this->spiFormFactory->create($this);
	}

	/**
	 * Create IQRF OTA upload service form
	 * @return Form IQRF OTA upload service form
	 */
	protected function createComponentConfigOtaUploadForm(): Form {
		return $this->otaFormFactory->create($this);
	}

}
