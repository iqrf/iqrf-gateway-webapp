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

use App\CoreModule\Exceptions\InvalidPasswordException;
use App\CoreModule\Models\UserManager;
use App\CoreModule\Presenters\ProfilePresenter;
use Nette\Forms\Form;
use Nette\Security\User;
use Nette\SmartObject;

/**
 * Change password form factory
 */
class ChangePasswordFormFactory {

	use SmartObject;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var UserManager User manager
	 */
	private $userManager;

	/**
	 * @var ProfilePresenter User profile presenter
	 */
	private $presenter;

	/**
	 * @var User User
	 */
	private $user;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param UserManager $userManager User manager
	 * @param User $user User
	 */
	public function __construct(FormFactory $factory, UserManager $userManager, User $user) {
		$this->factory = $factory;
		$this->userManager = $userManager;
		$this->user = $user;
	}

	/**
	 * Creates the change password form
	 * @param ProfilePresenter $presenter User profile presenter
	 * @return Form Change password form
	 */
	public function create(ProfilePresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('core.changePassword.form');
		$form->addPassword('oldPassword', 'oldPassword')
			->setRequired('messages.oldPassword');
		$form->addPassword('newPassword', 'newPassword')
			->setRequired('messages.newPassword');
		$form->addSubmit('change', 'change');
		$form->onSuccess[] = [$this, 'change'];
		return $form;
	}

	/**
	 * Changes the user's password
	 * @param Form $form Change password form
	 */
	public function change(Form $form): void {
		$values = $form->getValues();
		try {
			$userId = $this->user->getId();
			$this->userManager->changePassword($userId, $values['oldPassword'], $values['newPassword']);
			$this->user->logout();
			$this->presenter->flashMessage('core.changePassword.form.messages.success', 'success');
			$this->presenter->redirect('Sign:in');
		} catch (InvalidPasswordException $e) {
			$this->presenter->flashMessage('core.changePassword.form.messages.invalidOldPassword', 'danger');
		}
	}

}
