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

use App\ConfigModule\Forms\IqrfUartFormFactory;
use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\IqrfManager;
use App\CoreModule\Models\JsonFileManager;
use Nette\Forms\Form;
use Nette\Utils\JsonException;

/**
 * IQRF UART configuration presenter
 */
class IqrfUartPresenter extends GenericPresenter {

	/**
	 * @var IqrfUartFormFactory IQRF UART interface configuration form factory
	 * @inject
	 */
	public $formFactory;
	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;
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
		$components = ['iqrf::IqrfUart'];
		parent::__construct($components, $genericManager);
	}

	/**
	 * Render IQRF UART interface configurator
	 * @throws JsonException
	 */
	public function renderDefault(): void {
		$this->template->interfaces = $this->iqrfManager->getUartInterfaces();
		$this->template->pins = $this->fileManager->read('UartPins');
	}

	/**
	 * Create IQRF UART interface configuration form
	 * @return Form IQRF UART interface configuration form
	 * @throws JsonException
	 */
	protected function createComponentConfigIqrfUartForm(): Form {
		return $this->formFactory->create($this);
	}

}
