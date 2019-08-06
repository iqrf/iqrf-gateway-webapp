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
use App\CoreModule\Models\UserManager;
use App\CoreModule\Traits\TPresenterFlashMessage;
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
	 * @var UserManager User manager
	 */
	private $userManager;

	/**
	 * Signs user in
	 */
	public function actionIn(): void {
		if ($this->user->isLoggedIn()) {
			$this->redirect('Homepage:');
		}
	}

	/**
	 * Signs user out
	 */
	public function actionOut(): void {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		} else {
			$this->getUser()->logout();
		}
	}

	/**
	 * Injects the user manager
	 * @param UserManager $userManager User manager
	 */
	public function injectUserManager(UserManager $userManager): void {
		$this->userManager = $userManager;
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
		if ($this->userManager->getUsers() === []) {
			$this->redirect(':Install:Homepage:default');
		}
	}

}
