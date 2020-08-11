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

use App\CoreModule\Presenters\UserPresenter;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use Nette\Application\UI\Form;

/**
 * Edit an existing user form factory
 */
class UserEditFormFactory {

	/**
	 * Translation prefix
	 */
	private const PREFIX = 'core.user';

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var int User ID
	 */
	private $id;

	/**
	 * @var UserPresenter User manager presenter
	 */
	private $presenter;

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(FormFactory $factory, EntityManager $entityManager) {
		$this->factory = $factory;
		$this->entityManager = $entityManager;
	}

	/**
	 * Creates edit an existing user form
	 * @param UserPresenter $presenter User manager presenter
	 * @return Form Edit an existing user form
	 */
	public function create(UserPresenter $presenter): Form {
		$this->presenter = $presenter;
		$this->id = (int) $presenter->getParameter('id');
		$user = $presenter->getUser();
		$userTypes = [
			'normal' => 'userTypes.normal',
			'power' => 'userTypes.power',
		];
		$languages = [
			'en' => 'languages.en',
		];
		$form = $this->factory->create(self::PREFIX);
		$form->addText('username', 'username')
			->setRequired('messages.username');
		if ($user->isInRole('power')) {
			$form->addSelect('role', 'userType', $userTypes);
			$form->addSelect('language', 'language', $languages);
		}
		if ($user->getId() === $this->id) {
			$oldPassword = $form->addPassword('oldPassword', 'oldPassword');
			$newPassword = $form->addPassword('newPassword', 'newPassword');
			$oldPassword->addConditionOn($newPassword, Form::FILLED)
				->setRequired('messages.oldPassword');
			$newPassword->addConditionOn($oldPassword, Form::FILLED)
				->setRequired('messages.newPassword');
		}
		$form->addProtection('core.errors.form-timeout');
		$form->addSubmit('save', 'save');
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Saves the user info
	 * @param Form $form Edit an existing user form
	 */
	public function save(Form $form): void {
		$values = $form->getValues();
		$loggedUser = $this->presenter->getUser();
		$repository = $this->entityManager->getUserRepository();
		$user = $repository->find($this->id);
		assert($user instanceof User);
		if ($loggedUser->getId() === $this->id && $values->oldPassword !== '' &&
			$values->newPassword !== '') {
			if (!$user->verifyPassword($values->oldPassword)) {
				$this->presenter->flashError(self::PREFIX . '.messages.invalidOldPassword');
				return;
			}
			$user->setPassword($values->newPassword);
		}
		if (isset($values->username)) {
			$userWithName = $repository->findOneByUserName($values->username);
			if ($userWithName !== null && $userWithName->getId() !== $this->id) {
				$this->presenter->flashError(self::PREFIX . '.messages.usernameAlreadyExists');
				return;
			}
			$user->setUserName($values->username);
		}
		if (isset($values->role)) {
			$user->setRole($values->role);
		}
		if (isset($values->language)) {
			$user->setLanguage($values->language);
		}
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		if ($loggedUser->getId() === $this->id) {
			$loggedUser->logout();
		}
		$message = $form->getTranslator()->translate('messages.successEdit', ['username' => $user->getUserName()]);
		$this->presenter->flashSuccess($message);
		$this->presenter->redirect('User:default');
	}

}
