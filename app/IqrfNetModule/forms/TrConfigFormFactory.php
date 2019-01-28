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

namespace App\IqrfNetModule\Forms;

use App\CoreModule\Forms\FormFactory;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Models\TrConfigManager;
use App\IqrfNetModule\Presenters\DpaConfigPresenter;
use App\IqrfNetModule\Presenters\OsConfigPresenter;
use Nette\Forms\Form;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * IQRF TR configuration form factory
 */
abstract class TrConfigFormFactory {

	use SmartObject;

	/**
	 * @var mixed[] TR configuration
	 */
	protected $configuration = [];

	/**
	 * @var TrConfigManager IQRF TR configuration manager
	 */
	protected $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	protected $factory;

	/**
	 * @var DpaConfigPresenter|OsConfigPresenter IQMESH Network security presenter
	 */
	protected $presenter;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param TrConfigManager $manager IQRF TR configuration manager
	 */
	public function __construct(FormFactory $factory, TrConfigManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Load IQRF TR configuration into the form
	 */
	protected function load(): void {
		$address = $this->presenter->getParameter('id', 0);
		try {
			$dpa = $this->manager->read($address);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			return;
		}
		if (!array_key_exists('response', $dpa)) {
			return;
		}
		$this->configuration = $dpa['response']['data']['rsp'];
		if (array_key_exists('stdAndLpNetwork', $this->configuration)) {
			$this->configuration['stdAndLpNetwork'] = intval($this->configuration['stdAndLpNetwork']);
		}
	}

	/**
	 * Write IQRF TR configuration from the form
	 * @param Form $form Set TR configuration form
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function save(Form $form): void {
		$address = $this->presenter->getParameter('id', 0);
		$config = $form->getValues(true);
		if (array_key_exists('stdAndLpNetwork', $config)) {
			$config['stdAndLpNetwork'] = boolval($config['stdAndLpNetwork']);
		}
		// Workaround for a bug in IQRF Gateway Daemon
		$config['rfBand'] = $this->configuration['rfBand'];
		$this->manager->write($address, $config);
	}

}
