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

declare(strict_types=1);

namespace App\Forms;

use App\Forms\FormFactory;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\User;

/**
 * Sign in form factory.
 */
class SignInFormFactory {

	use Nette\SmartObject;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param User $user
	 */
	public function __construct(FormFactory $factory, User $user) {
		$this->factory = $factory;
		$this->user = $user;
	}

	/**
	 * Create sign in form
	 * @param callable $onSuccess
	 * @return Form Sign in form
	 */
	public function create(callable $onSuccess): Form {
		$form = $this->factory->create();
		$form->addText('username', 'Username:')->setRequired('Please enter your username.');
		$form->addPassword('password', 'Password:')->setRequired('Please enter your password.');
		$form->addCheckbox('remember', 'Keep me signed in');
		$form->addSubmit('send', 'Sign in');
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
			try {
				$this->user->setExpiration($values->remember ? '14 days' : '20 minutes');
				$this->user->login($values->username, $values->password);
			} catch (AuthenticationException $e) {
				$form->addError('The username or password you entered is incorrect.');
				return;
			}
			$onSuccess();
		};
		return $form;
	}

}
