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
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Forms\ChangeAddressFormFactory;
use App\IqrfNetModule\Forms\SecurityFormFactory;
use App\IqrfNetModule\Models\EnumerationManager;
use App\IqrfNetModule\Models\TrConfigManager;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\JsonException;

/**
 * TR configuration presenter
 */
class TrConfigPresenter extends ProtectedPresenter {

	/**
	 * @var Cache Cache
	 */
	private $cache;

	/**
	 * @var ChangeAddressFormFactory Change device address form
	 * @inject
	 */
	public $changeAddressForm;

	/**
	 * @var SecurityFormFactory IQMESH Security configuration form
	 * @inject
	 */
	public $securityFormFactory;

	/**
	 * @var EnumerationManager IQRF TR enumeration manager
	 */
	private $enumerationManager;

	/**
	 * @var TrConfigManager IQRF TR configuration manager
	 */
	protected $manager;

	/**
	 * Constructor
	 * @param EnumerationManager $enumerationManager IQRF TR enumeration manager
	 * @param IStorage $storage Cache storage
	 */
	public function __construct(EnumerationManager $enumerationManager, IStorage $storage) {
		$this->cache = new Cache($storage, 'trConfiguration');
		$this->enumerationManager = $enumerationManager;
		parent::__construct();
	}

	/**
	 * Create the change a network device address form
	 * @return Form Change a network device address form
	 */
	protected function createComponentIqrfNetAddressForm(): Form {
		return $this->changeAddressForm->create($this);
	}

	/**
	 * Creates the IQMESH Security configuration form
	 * @return Form IQMESH Security configuration form
	 */
	protected function createComponentIqrfNetSecurityForm(): Form {
		return $this->securityFormFactory->create($this);
	}

	/**
	 * Loads TR configuration
	 * @param int $address TR address
	 */
	public function loadConfiguration(int $address): void {
		$this->template->configuration = false;
		if ($this->cache->load('trConfiguration' . $address) !== null &&
			$this->cache->load('dpaVersion' . $address) !== null &&
			$this->cache->load('dpaHandlerDetected' . $address) !== null) {
			if (!$this->getUser()->isInRole('power')) {
				$this->flashSuccess('iqrfnet.trConfiguration.read.success');
			}
			$this->template->configuration = true;
			return;
		}
		try {
			$enumeration = $this->enumerationManager->device($address);
			if (!array_key_exists('response', $enumeration)) {
				$this->cache->clean([Cache::ALL]);
				$this->flashError('iqrfnet.trConfiguration.read.failure');
				$this->redrawControl('forms');
				return;
			}
			$response = $enumeration['response']->data->rsp;
			$dpaVersion = $response->peripheralEnumeration->dpaVer ?? '99.99';
			$this->cache->save('dpaVersion' . $address, ltrim($dpaVersion, '0'));
			$this->cache->save('dpaHandlerDetected' . $address, $response->osRead->flags->dpaHandlerDetected);
			$configuration = $response->trConfiguration;
			if (property_exists($configuration, 'stdAndLpNetwork')) {
				$configuration->stdAndLpNetwork = (int) $configuration->stdAndLpNetwork;
			}
			if (!$this->getUser()->isInRole('power')) {
				$this->flashSuccess('iqrfnet.trConfiguration.read.success');
			}
			$this->template->configuration = true;
			$this->cache->save('trConfiguration' . $address, $configuration, [Cache::EXPIRE => '5 minutes']);
		} catch (DpaErrorException | EmptyResponseException | JsonException | UserErrorException $e) {
			$this->cache->clean([Cache::ALL]);
			$this->flashError('iqrfnet.trConfiguration.read.failure');
		}
	}

	/**
	 * Renders TR configuration page
	 * @param int $address TR address
	 */
	public function renderDefault(int $address = 0): void {
		$this->template->address = $address;
		if (!$this->isAjax()) {
			$this->cache->remove('trConfiguration' . $address);
		}
		$this->loadConfiguration($address);
		if ($this->isAjax()) {
			$this->redrawControl('forms');
		}
	}

}
