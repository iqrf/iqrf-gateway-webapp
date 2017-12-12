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

declare(strict_types=1);

namespace App\IqrfAppModule\Presenters;

use App\IqrfAppModule\Forms\IqrfNetBondingFormFactory;
use App\IqrfAppModule\Forms\IqrfNetDiscoveryFormFactory;
use App\Presenters\BasePresenter;

/**
 * IQMESH Network Manager presenter
 */
class NetworkPresenter extends BasePresenter {

	/**
	 * @var IqrfNetBondingFormFactory
	 */
	private $bondingForm;

	/**
	 *
	 * @var IqrfNetDiscoveryFormFactory
	 */
	private $discoveryForm;

	/**
	 * Constructor
	 * @param IqrfNetBondingFormFactory $bondingForm
	 * @param IqrfNetDiscoveryFormFactory $discoveryForm
	 */
	public function __construct(IqrfNetBondingFormFactory $bondingForm, IqrfNetDiscoveryFormFactory $discoveryForm) {
		$this->bondingForm = $bondingForm;
		$this->discoveryForm = $discoveryForm;
	}

	/**
	 * Render default page
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
	}

	/**
	 * Create IQMESH Bonding form
	 * @return Form IQMESH Bonding form
	 */
	protected function createComponentIqrfNetBondingForm() {
		$this->onlyForAdmins();
		return $this->bondingForm->create($this);
	}

	/**
	 * Create IQMESH Discovery form
	 * @return Form IQMESH Discovery form
	 */
	protected function createComponentIqrfNetDiscoveryForm() {
		$this->onlyForAdmins();
		return $this->discoveryForm->create($this);
	}

}
