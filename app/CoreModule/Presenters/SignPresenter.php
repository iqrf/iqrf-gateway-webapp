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

namespace App\CoreModule\Presenters;

use App\CoreModule\Forms\SignInFormFactory;
use App\CoreModule\Traits\TPresenterFlashMessage;
use App\Models\Database\EntityManager;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Nette\Application\UI\Form;

/**
 * Sign in/out presenter
 */
class SignPresenter extends BasePresenter {

	use TPresenterFlashMessage;

	/**
	 * @var string Back link
	 * @persistent
	 */
	public $backlink;

	/**
	 * @var SignInFormFactory Sign in form factory
	 * @inject
	 */
	public $signInFactory;

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * Signs user in
	 */
	public function actionIn(): void {
		if ($this->getUser()->isLoggedIn()) {
			$this->redirect('Homepage:');
		}
	}

	/**
	 * Signs user out
	 */
	public function actionOut(): void {
		if ($this->getUser()->isLoggedIn()) {
			$this->getUser()->logout();
		}
	}

	/**
	 * Injects the entity manager
	 * @param EntityManager $entityManager Entity manager
	 */
	public function injectEntityManager(EntityManager $entityManager): void {
		$this->entityManager = $entityManager;
	}

	/**
	 * Creates the sign in form
	 * @return Form Sign in form
	 */
	protected function createComponentSignInForm(): Form {
		return $this->signInFactory->create($this);
	}

	/**
	 * Starts up an base presenter
	 */
	protected function startup(): void {
		parent::startup();
		try {
			if ($this->entityManager->getUserRepository()->count([]) === 0) {
				$this->redirect(':Install:Homepage:default');
			}
		} catch (TableNotFoundException $e) {
			$this->redirect(':Install:Error:missingTable');
		} catch (DriverException $e) {
			$this->redirect(':Install:Error:missingSqlDriver');
		}
	}

}
