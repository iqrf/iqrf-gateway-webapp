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

use App\ConfigModule\Forms\JsonDpaApiRawFormFactory;
use App\Presenters\ProtectedPresenter;
use Nette\Forms\Form;

/**
 * JSON Raw API configuration presenter
 */
class JsonRawApiPresenter extends ProtectedPresenter {

	/**
	 * @var JsonDpaApiRawFormFactory JSON Raw API form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * Create JSON Raw API settings form
	 * @return Form JSON Raw API settings form
	 */
	protected function createComponentConfigJsonDpaApiRawForm(): Form {
		return $this->formFactory->create($this);
	}

}
