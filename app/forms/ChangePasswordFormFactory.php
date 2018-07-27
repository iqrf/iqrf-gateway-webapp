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

namespace App\Forms;

use App\Forms\FormFactory;
use App\Presenters\ProfilePresenter;
use App\Model\InvalidPassword;
use App\Model\UserManager;
use Nette;
use Nette\Forms\Form;
use Nette\Security\User;

/**
 * Change password form factory.
 */
class ChangePasswordFormFactory {

	use Nette\SmartObject;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var UserManager User manager
	 */
	private $userManager;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param User $user
	 */
	public function __construct(FormFactory $factory, UserManager $userManager, User $user) {
		$this->factory = $factory;
		$this->userManager = $userManager;
		$this->user = $user;
	}

	/**
	 * Create change password form
	 * @param ProfilePresenter Profile presenter
	 * @return Form Change password form
	 */
	public function create(ProfilePresenter $presenter): Form {
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('core.changePassword.form'));
		$form->addPassword('oldPassword', 'oldPassword')->setRequired('messages.oldPassword');
		$form->addPassword('newPassword', 'newPassword')->setRequired('messages.newPassword');
		$form->addSubmit('change', 'change');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			try {
				$userId = $this->user->getId();
				$this->userManager->changePassword($userId, $values['oldPassword'], $values['newPassword']);
				$this->user->logout();
				$presenter->flashMessage('core.changePassword.form.messages.success', 'success');
				$presenter->redirect('Sign:in');
			} catch (\Exception $e) {
				if ($e instanceof InvalidPassword) {
					$presenter->flashMessage('core.changePassword.form.messages.invalidOldPassword', 'danger');
				} else {
					throw $e;
				}
			}
		};
		return $form;
	}

}
