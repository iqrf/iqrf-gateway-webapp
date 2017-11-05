<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
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

namespace App\IqrfAppModule\Presenters;

use App\IqrfAppModule\Forms\IqrfNetBondNodeFormFactory;
use App\Presenters\BasePresenter;

/**
 * IQMESH Network Manager presenter
 */
class NetworkPresenter extends BasePresenter {

	/**
	 * @var IqrfNetBondNodeFormFactory
	 */
	private $bondNodeForm;
	
	/**
	 * Constructor
	 * @param IqrfNetBondNodeFormFactory $bondNodeForm
	 */
	public function __construct(IqrfNetBondNodeFormFactory $bondNodeForm) {
		$this->bondNodeForm = $bondNodeForm;
	}
	
	/**
	 * Render default page
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
	}

	/**
	 * Create bond node form form
	 * @return Form
	 */
	protected function createComponentIqrfNetBondNodeForm() {
		$this->onlyForAdmins();
		return $this->bondNodeForm->create($this);
	}

}
