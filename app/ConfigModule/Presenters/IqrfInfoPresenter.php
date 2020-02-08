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


use App\ConfigModule\Forms\IqrfInfoFormFactory;
use App\ConfigModule\Models\GenericManager;
use Nette\Application\UI\Form;
use Nette\Utils\JsonException;

/**
 * IQRF Info configuration presenter
 */
class IqrfInfoPresenter extends GenericPresenter {

	/**
	 * @var IqrfInfoFormFactory IQRF Info configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * Constructor
	 * @param GenericManager $genericManager Generic configuration manager
	 */
	public function __construct(GenericManager $genericManager) {
		$components = ['iqrf::IqrfInfo'];
		parent::__construct($components, $genericManager);
	}

	/**
	 * Creates the IQRF Info configuration form
	 * @return Form IQRF Info configuration form
	 * @throws JsonException
	 */
	protected function createComponentConfigIqrfInfoForm(): Form {
		return $this->formFactory->create($this);
	}

}
