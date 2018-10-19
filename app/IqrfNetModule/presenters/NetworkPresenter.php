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

namespace App\IqrfNetModule\Presenters;

use App\CoreModule\Presenters\ProtectedPresenter;
use App\IqrfNetModule\Forms\BondingFormFactory;
use App\IqrfNetModule\Forms\DiscoveryFormFactory;
use App\IqrfNetModule\Forms\RfFormFactory;
use App\IqrfNetModule\Forms\SecurityFormFactory;
use Nette\Forms\Form;
use Nette\Utils\JsonException;

/**
 * IQMESH Network Manager presenter
 */
class NetworkPresenter extends ProtectedPresenter {

	/**
	 * @var BondingFormFactory IQMESH Bonding form
	 * @inject
	 */
	public $bondingForm;

	/**
	 * @var DiscoveryFormFactory IQMESH Discovery form
	 * @inject
	 */
	public $discoveryForm;

	/**
	 * @var RfFormFactory IQMESH RF configuration form
	 * @inject
	 */
	public $rfForm;

	/**
	 * @var SecurityFormFactory IQMESH Security configuration form
	 * @inject
	 */
	public $securityForm;

	/**
	 * Create IQMESH Bonding form
	 * @return Form IQMESH Bonding form
	 */
	protected function createComponentIqrfNetBondingForm(): Form {
		return $this->bondingForm->create($this);
	}

	/**
	 * Create IQMESH Discovery form
	 * @return Form IQMESH Discovery form
	 */
	protected function createComponentIqrfNetDiscoveryForm(): Form {
		return $this->discoveryForm->create($this);
	}

	/**
	 * Create IQMESH RF configuration form
	 * @return Form IQMESH RF configuration form
	 * @throws JsonException
	 */
	protected function createComponentIqrfNetRfForm(): Form {
		return $this->rfForm->create($this);
	}

	/**
	 * Create IQMESH Security configuration form
	 * @return Form IQMESH Security configuration form
	 */
	protected function createComponentIqrfNetSecurityForm(): Form {
		return $this->securityForm->create($this);
	}

}
