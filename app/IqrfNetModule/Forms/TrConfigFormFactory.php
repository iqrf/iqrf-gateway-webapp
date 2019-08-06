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

namespace App\IqrfNetModule\Forms;

use App\CoreModule\Forms\FormFactory;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Models\TrConfigManager;
use App\IqrfNetModule\Presenters\DpaConfigPresenter;
use App\IqrfNetModule\Presenters\RfConfigPresenter;
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
	 * @var DpaConfigPresenter|RfConfigPresenter IQMESH Network security presenter
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
	 * Loads IQRF TR configuration into the form
	 */
	protected function load(): void {
		$address = intval($this->presenter->getParameter('address', 0));
		try {
			$dpa = $this->manager->read($address);
		} catch (DpaErrorException | EmptyResponseException | JsonException | UserErrorException $e) {
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
	 * Writes IQRF TR configuration from the form
	 * @param Form $form Set TR configuration form
	 */
	public function save(Form $form): void {
		$address = $this->presenter->getParameter('id', 0);
		$config = $form->getValues(true);
		if (array_key_exists('stdAndLpNetwork', $config)) {
			$config['stdAndLpNetwork'] = boolval($config['stdAndLpNetwork']);
		}
		try {
			$this->manager->write($address, $config);
			$this->presenter->flashSuccess('iqrfnet.trConfiguration.write.success');
		} catch (DpaErrorException | EmptyResponseException | JsonException | UserErrorException $e) {
			$this->presenter->flashError('iqrfnet.trConfiguration.write.failure');
		}
	}

}
