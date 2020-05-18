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

use App\ConfigModule\Forms\JsonMngMetaDataApiFormFactory;
use Nette\Application\UI\Form;

/**
 * JSON Metadata API configuration presenter
 */
class JsonMngMetaDataApiPresenter extends GenericPresenter {

	/**
	 * IQRF Gateway Daemon component name
	 */
	private const COMPONENT = 'iqrf::JsonMngMetaDataApi';

	/**
	 * @var JsonMngMetaDataApiFormFactory JSON Metadata API form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * Renders the JSON Raw API configurator
	 */
	public function actionDefault(): void {
		$this->loadFormConfiguration($this['configJsonMngMetaDataApiForm'], self::COMPONENT, null);
	}

	/**
	 * Creates the JSON Metadata API configuration form
	 * @return Form JSON Metadata API configuration form
	 */
	protected function createComponentConfigJsonMngMetaDataApiForm(): Form {
		return $this->formFactory->create($this);
	}

}
