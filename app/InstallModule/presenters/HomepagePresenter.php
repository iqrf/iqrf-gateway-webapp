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

namespace App\InstallModule\Presenters;

use App\CoreModule\Forms\UserAddFormFactory;
use App\CoreModule\Model\UserManager;
use App\CoreModule\Presenters\BasePresenter;
use Nette\Forms\Form;

/**
 * Installation presenter
 */
class HomepagePresenter extends BasePresenter {

	/**
	 * @var UserAddFormFactory Add a new user form factory
	 * @inject
	 */
	public $userFormFactory;

	/**
	 * @var UserManager User manager
	 */
	private $userManager;

	/**
	 * Inject user manager
	 * @param UserManager $userManager User manager
	 */
	public function injectUserManager(UserManager $userManager): void {
		$this->userManager = $userManager;
	}

	/**
	 * Create add a new user form
	 * @return Form Add a new user form
	 */
	protected function createComponentRegUserForm(): Form {
		return $this->userFormFactory->create($this);
	}

	/**
	 * Start up an base presenter
	 */
	protected function startup(): void {
		parent::startup();
		if ($this->userManager->getUsers() !== []) {
			$this->redirect(':Core:Sign:in');
		}
	}

}
