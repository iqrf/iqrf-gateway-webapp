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
use App\IqrfAppModule\Forms\IqrfNetAccessPasswordFormFactory;
use App\Presenters\BasePresenter;
use Nette\Application\UI\Form;

/**
 * IQMESH Network Manager presenter
 */
class NetworkPresenter extends BasePresenter {

	/**
	 * @var IqrfNetBondingFormFactory IQMESH Bonding form
	 */
	private $bondingForm;

	/**
	 * @var IqrfNetDiscoveryFormFactory IQMESH Discovery form
	 */
	private $discoveryForm;

	/**
	 * @var IqrfNetAccessPasswordFormFactory IQMESH Access Password form
	 */
	private $accesspasswordForm;

	/**
	 * Constructor
	 * @param IqrfNetBondingFormFactory $bondingForm IQMESH Bonding form
	 * @param IqrfNetDiscoveryFormFactory $discoveryForm IQMESH Discovery form
	 * @param IqrfNetAccessPasswordFormFactory $accesspasswordForm IQMESH Access password form
	 */
	public function __construct(IqrfNetBondingFormFactory $bondingForm, IqrfNetDiscoveryFormFactory $discoveryForm, IqrfNetAccessPasswordFormFactory $accesspasswordForm) {
		$this->bondingForm = $bondingForm;
		$this->discoveryForm = $discoveryForm;
		$this->accesspasswordForm = $accesspasswordForm;
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
	protected function createComponentIqrfNetBondingForm(): Form {
		$this->onlyForAdmins();
		return $this->bondingForm->create($this);
	}

	/**
	 * Create IQMESH Discovery form
	 * @return Form IQMESH Discovery form
	 */
	protected function createComponentIqrfNetDiscoveryForm(): Form {
		$this->onlyForAdmins();
		return $this->discoveryForm->create($this);
	}

	/**
	 * Create IQMESH Access Password form
	 * @return Form IQMESH Access Password form
	 */
	protected function createComponentIqrfNetAccessPasswordForm(): Form {
		$this->onlyForAdmins();
		return $this->accesspasswordForm->create($this);
	}

}
