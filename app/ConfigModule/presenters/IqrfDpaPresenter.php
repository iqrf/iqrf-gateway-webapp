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

use App\ConfigModule\Forms\IqrfDpaFormFactory;
use App\ConfigModule\Model\GenericManager;
use Nette\Forms\Form;
use Nette\Utils\JsonException;

/**
 * IQRF DPA interface configuration presenter
 */
class IqrfDpaPresenter extends GenericPresenter {

	/**
	 * @var IqrfDpaFormFactory IQRF DPA interface configuration form factory
	 * @inject
	 */
	public $dpaFormFactory;

	/**
	 * Constructor
	 * @param GenericManager $genericManager Generic configuration manager
	 */
	public function __construct(GenericManager $genericManager) {
		$components = ['iqrf::IqrfDpa'];
		parent::__construct($components, $genericManager);
	}

	/**
	 * Create IQRF DPA configuration interface form
	 * @return Form IQRF DPA configuration interface form
	 * @throws JsonException
	 */
	protected function createComponentConfigIqrfDpaForm(): Form {
		return $this->dpaFormFactory->create($this);
	}

}
