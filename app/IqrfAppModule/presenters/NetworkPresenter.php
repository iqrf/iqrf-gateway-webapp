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
declare(strict_types=1);

namespace App\IqrfAppModule\Presenters;

use App\IqrfAppModule\Forms\IqrfNetBondingFormFactory;
use App\IqrfAppModule\Forms\IqrfNetDiscoveryFormFactory;
use App\IqrfAppModule\Forms\IqrfNetRfFormFactory;
use App\IqrfAppModule\Forms\IqrfNetSecurityFormFactory;
use App\Presenters\BasePresenter;
use Nette\Forms\Form;

/**
 * IQMESH Network Manager presenter
 */
class NetworkPresenter extends BasePresenter {

	/**
	 * @var IqrfNetBondingFormFactory IQMESH Bonding form
	 * @inject
	 */
	public $bondingForm;

	/**
	 * @var IqrfNetDiscoveryFormFactory IQMESH Discovery form
	 * @inject
	 */
	public $discoveryForm;

	/**
	 * @var IqrfNetRfFormFactory IQMESH RF form
	 * @inject
	 */
	public $rfForm;

	/**
	 * @var IqrfNetSecurityFormFactory IQMESH Security form
	 * @inject
	 */
	public $securityForm;

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
	protected function createComponentIqrfNetBondingForm(): Form {
		$this->onlyForAdmins();
		return $this->bondingForm->create();
	}

	/**
	 * Create IQMESH Discovery form
	 * @return Form IQMESH Discovery form
	 */
	protected function createComponentIqrfNetDiscoveryForm(): Form {
		$this->onlyForAdmins();
		return $this->discoveryForm->create();
	}

	/**
	 * Create IQMESH RF form
	 * @return Form IQMESH RF form
	 */
	protected function createComponentIqrfNetRfForm(): Form {
		$this->onlyForAdmins();
		return $this->rfForm->create();
	}

	/**
	 * Create IQMESH Security form
	 * @return Form IQMESH Security form
	 */
	protected function createComponentIqrfNetSecurityForm(): Form {
		$this->onlyForAdmins();
		return $this->securityForm->create();
	}

}
