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

use App\CoreModule\Presenters\ProtectedPresenter;
use App\CoreModule\Traits\TPresenterFlashMessage;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Models\EnumerationManager;
use Iqrf\Repository\Exceptions\ProductNotFound;
use Iqrf\Repository\Exceptions\ServiceUnavailable;
use Iqrf\Repository\Models\ProductManager;
use Nette\Utils\JsonException;

/**
 * IQMESH device enumeration presenter
 */
class EnumerationPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

	/**
	 * @var EnumerationManager IQMESH Enumeration manager
	 */
	private $manager;

	/**
	 * @var ProductManager IQRF Repository product manager
	 */
	private $productManager;

	/**
	 * Constructor
	 * @param EnumerationManager $manager IQMESH Enumeration manager
	 * @param ProductManager $productManager IQRF Repository product manager
	 */
	public function __construct(EnumerationManager $manager, ProductManager $productManager) {
		$this->manager = $manager;
		$this->productManager = $productManager;
		parent::__construct();
	}

	/**
	 * Enumerates a device
	 * @param int $address Device address
	 */
	public function renderDefault(int $address = 0): void {
		try {
			$data = $this->manager->device($address)['response']->data->rsp;
			if (isset($data)) {
				$this->template->data = $data;
				$this->template->osData = $data->osRead;
				$this->template->peripheralData = $data->peripheralEnumeration;
				try {
					$hwpId = $data->peripheralEnumeration->hwpId;
					$this->template->product = $this->productManager->get($hwpId);
				} catch (ProductNotFound $e) {
					// Do nothing
					// TODO: add info flash message
				} catch (ServiceUnavailable $e) {
					$this->flashWarning('iqrfnet.enumeration.messages.repositoryUnavailable');
				}
			} else {
				$this->flashError('iqrfnet.enumeration.messages.failure');
				$this->redirect('Network:default');
			}
		} catch (DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->flashError('iqrfnet.enumeration.messages.failure');
			$this->redirect('Network:default');
		}
	}

}
