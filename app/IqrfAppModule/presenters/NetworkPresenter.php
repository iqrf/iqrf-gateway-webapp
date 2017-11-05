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
use App\IqrfAppModule\Forms\IqrfNetRebondNodeFormFactory;
use App\IqrfAppModule\Forms\IqrfNetRemoveNodeFormFactory;
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
	 * @var IqrfNetRebondNodeFormFactory
	 */
	private $rebondNodeForm;

	/**
	 * @var IqrfNetRemoveNodeFormFactory
	 */
	private $removeNodeForm;

	/**
	 * Constructor
	 * @param IqrfNetBondNodeFormFactory $bondNodeForm
	 * @param IqrfNetRebondNodeFormFactory $rebondNodeForm
	 * @param IqrfNetRemoveNodeFormFactory $removeNodeForm
	 */
	public function __construct(IqrfNetBondNodeFormFactory $bondNodeForm, IqrfNetRebondNodeFormFactory $rebondNodeForm, IqrfNetRemoveNodeFormFactory $removeNodeForm) {
		$this->bondNodeForm = $bondNodeForm;
		$this->rebondNodeForm = $rebondNodeForm;
		$this->removeNodeForm = $removeNodeForm;
	}

	/**
	 * Render default page
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
	}

	/**
	 * Create bond node form
	 * @return Form bond node form
	 */
	protected function createComponentIqrfNetBondNodeForm() {
		$this->onlyForAdmins();
		return $this->bondNodeForm->create($this);
	}

	/**
	 * Create rebond node form
	 * @return Form rebond node form
	 */
	protected function createComponentIqrfNetRebondNodeForm() {
		$this->onlyForAdmins();
		return $this->rebondNodeForm->create($this);
	}

	/**
	 * Create remove node form
	 * @return Form bond node form
	 */
	protected function createComponentIqrfNetRemoveNodeForm() {
		$this->onlyForAdmins();
		return $this->removeNodeForm->create($this);
	}

}
