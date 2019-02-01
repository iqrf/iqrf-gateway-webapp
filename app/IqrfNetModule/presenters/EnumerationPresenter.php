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

namespace App\IqrfNetModule\Presenters;

use App\CoreModule\Presenters\ProfilePresenter;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Models\EnumerationManager;
use Nette\Utils\JsonException;

/**
 * IQMESH Network manager - device enumeration
 */
class EnumerationPresenter extends ProfilePresenter {

	/**
	 * @var EnumerationManager IQMESH Enumeration manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param EnumerationManager $manager IQMESH Enumeration manager
	 */
	public function __construct(EnumerationManager $manager) {
		$this->manager = $manager;
		parent::__construct();
	}

	/**
	 * Enumerate device
	 * @param int $address Device address
	 */
	public function renderDefault(int $address = 0): void {
		try {
			$enumeration = $this->manager->device($address);
			$data = $enumeration['response']['data']['rsp'];
			if (isset($data)) {
				$this->template->data = $data;
				$this->template->osData = $data['osRead'];
				$this->template->peripheralData = $data['peripheralEnumeration'];
			} else {
				$this->flashMessage('iqrfnet.enumeration.messages.failure', 'danger');
				$this->redirect('Network:default');
			}
		} catch (DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->flashMessage('iqrfnet.enumeration.messages.failure', 'danger');
			$this->redirect('Network:default');
		}
	}

}
