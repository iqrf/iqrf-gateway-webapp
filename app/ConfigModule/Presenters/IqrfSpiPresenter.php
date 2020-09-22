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

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Forms\IqrfSpiFormFactory;
use Nette\Application\UI\Form;

/**
 * IQRF SPI interface configuration presenter
 */
class IqrfSpiPresenter extends GenericPresenter {

	/**
	 * IQRF Gateway Daemon component name
	 */
	private const COMPONENT = 'iqrf::IqrfSpi';

	/**
	 * @var IqrfSpiFormFactory IQRF SPI interface configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * Renders the IQRF SPI interface configurator
	 */
	public function actionDefault(): void {
		$this->loadFormConfiguration($this['configIqrfSpiForm'], self::COMPONENT, null);
	}

	/**
	 * Creates the IQRF SPI interface configuration form
	 * @return Form IQRF SPI interface configuration form
	 */
	protected function createComponentConfigIqrfSpiForm(): Form {
		return $this->formFactory->create($this);
	}

}
