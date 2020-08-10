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

namespace App\InstallModule\Presenters;

use App\CoreModule\Presenters\BasePresenter;
use App\CoreModule\Traits\TPresenterFlashMessage;
use App\Models\Database\EntityManager;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\TableNotFoundException;

/**
 * Installation presenter
 */
abstract class InstallationPresenter extends BasePresenter {

	use TPresenterFlashMessage;

	/**
	 * @var EntityManager Entity manager
	 */
	protected $entityManager;

	/**
	 * Inject the entity manager
	 * @param EntityManager $entityManager Entity manager
	 */
	public function injectUserManager(EntityManager $entityManager): void {
		$this->entityManager = $entityManager;
	}

	/**
	 * Starts up an base presenter
	 */
	protected function startup(): void {
		parent::startup();
		try {
			if ($this->entityManager->getUserRepository()->count([]) !== 0) {
				$this->redirect(':Core:Sign:in');
			}
			if ($this->isLinkCurrent(':Install:Error:missingTable') ||
				$this->isLinkCurrent(':Install:Error:missingSqlDriver')) {
				$this->redirect(':Install:Homepage:default');
			}
		} catch (TableNotFoundException $e) {
			if (!$this->isLinkCurrent(':Install:Error:missingTable')) {
				$this->redirect(':Install:Error:missingTable');
			}
		} catch (DriverException $e) {
			if (!$this->isLinkCurrent(':Install:Error:missingSqlDriver')) {
				$this->redirect(':Install:Error:missingSqlDriver');
			}
		}
	}

}
