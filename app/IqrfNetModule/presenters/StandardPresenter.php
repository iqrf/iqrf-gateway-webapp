<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

namespace App\IqrfNetModule\Presenters;

use App\CoreModule\Presenters\ProtectedPresenter;
use App\IqrfNetModule\Forms\StandardBinaryOutputFormFactory;
use App\IqrfNetModule\Forms\StandardLightFormFactory;
use App\IqrfNetModule\Forms\StandardSensorFormFactory;
use Nette\Forms\Form;

/**
 * IQRF Standard manager presenter
 */
class StandardPresenter extends ProtectedPresenter {

	/**
	 * @var StandardBinaryOutputFormFactory IQRF Standard binary output form factory
	 * @inject
	 */
	public $binaryOutputForm;

	/**
	 * @var StandardLightFormFactory IQRF Standard light form factory
	 * @inject
	 */
	public $lightForm;

	/**
	 * @var StandardSensorFormFactory IQRF Standard sensor form factory
	 * @inject
	 */
	public $sensorForm;

	/**
	 * AJAX handler for showing IQRF Standard binary output info
	 * @param mixed[] $data API request and response
	 */
	public function handleBinaryOutputResponse(array $data): void {
		$this->template->binaryOutputData = $data;
		$this->redrawControl('binaryOutputs');
	}

	/**
	 * AJAX handler for showing IQRF Standard light info
	 * @param mixed[] $data API request and response
	 */
	public function handleLightResponse(array $data): void {
		$this->template->lightData = $data;
		$this->redrawControl('lights');
	}

	/**
	 * AJAX handler for showing IQRF Standard sensor info
	 * @param mixed[] $data API request and response
	 */
	public function handleSensorResponse(array $data): void {
		$this->template->sensorData = $data;
		$this->redrawControl('sensors');
	}

	/**
	 * Create IQRF Standard binary output form
	 * @return Form IQRF Standard binary output form
	 */
	protected function createComponentBinaryOutputForm(): Form {
		return $this->binaryOutputForm->create($this);
	}

	/**
	 * Create IQRF Standard light form
	 * @return Form IQRF Standard light form
	 */
	protected function createComponentLightForm(): Form {
		return $this->lightForm->create($this);
	}

	/**
	 * Create IQRF Standard sensor form
	 * @return Form IQRF Standard sensor form
	 */
	protected function createComponentSensorForm(): Form {
		return $this->sensorForm->create($this);
	}

}
