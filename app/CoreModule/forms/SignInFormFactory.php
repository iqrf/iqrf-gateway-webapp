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

namespace App\CoreModule\Forms;

use App\CoreModule\Presenters\SignPresenter;
use Nette\Forms\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\User;
use Nette\SmartObject;

/**
 * Sign in form factory
 */
class SignInFormFactory {

	use SmartObject;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var SignPresenter Sign (in|out) presenter
	 */
	private $presenter;

	/**
	 * @var User User object
	 */
	private $user;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param User $user User object
	 */
	public function __construct(FormFactory $factory, User $user) {
		$this->factory = $factory;
		$this->user = $user;
	}

	/**
	 * Creates sign in form
	 * @param SignPresenter $presenter Sign (in|out) presenter
	 * @return Form Sign in form
	 */
	public function create(SignPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('core.sign.inForm');
		$form->addText('username', 'username')
			->setRequired('messages.username');
		$form->addPassword('password', 'password')
			->setRequired('messages.password');
		$form->addCheckbox('remember', 'remember');
		$form->addSubmit('send', 'send');
		$form->onSuccess[] = [$this, 'signIn'];
		return $form;
	}

	/**
	 * Signs user in
	 * @param Form $form Sign in form
	 */
	public function signIn(Form $form): void {
		$values = $form->getValues();
		try {
			$this->user->setExpiration($values->remember ? '14 days' : '20 minutes');
			$this->user->login($values->username, $values->password);
			$this->presenter->flashSuccess('core.sign.inForm.messages.success');
			$this->presenter->restoreRequest($this->presenter->backlink);
			$this->presenter->redirect('Homepage:default');
		} catch (AuthenticationException $e) {
			$this->presenter->flashError('core.sign.inForm.messages.incorrectUsernameOrPassword');
		}
	}

}
