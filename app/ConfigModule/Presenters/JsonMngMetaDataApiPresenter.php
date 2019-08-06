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
use App\ConfigModule\Models\GenericManager;
use Nette\Forms\Form;
use Nette\Utils\JsonException;

/**
 * JSON Metadata API configuration presenter
 */
class JsonMngMetaDataApiPresenter extends GenericPresenter {

	/**
	 * @var JsonMngMetaDataApiFormFactory JSON Metadata API form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * Constructor
	 * @param GenericManager $genericManager Generic configuration manager
	 */
	public function __construct(GenericManager $genericManager) {
		$components = ['iqrf::JsonMngMetaDataApi'];
		parent::__construct($components, $genericManager);
	}

	/**
	 * Creates the JSON Metadata API configuration form
	 * @return Form JSON Metadata API configuration form
	 * @throws JsonException
	 */
	protected function createComponentConfigJsonMngMetaDataApiForm(): Form {
		return $this->formFactory->create($this);
	}

}
