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

use App\CoreModule\Exceptions\UsernameAlreadyExistsException;
use App\CoreModule\Models\UserManager;
use App\CoreModule\Presenters\BasePresenter;
use App\CoreModule\Presenters\UserPresenter;
use App\InstallModule\Presenters\CreateUserPresenter;
use Nette\Application\UI\Form;

/**
 * Register a new user form factory
 */
class UserAddFormFactory {

	/**
	 * Translation prefix
	 */
	private const PREFIX = 'core.user';

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var BasePresenter Base presenter
	 */
	private $presenter;

	/**
	 * @var UserManager User manager
	 */
	private $userManager;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param UserManager $userManager User manager
	 */
	public function __construct(FormFactory $factory, UserManager $userManager) {
		$this->factory = $factory;
		$this->userManager = $userManager;
	}

	/**
	 * Creates register a new user form
	 * @param BasePresenter|CreateUserPresenter|UserPresenter $presenter Base presenter
	 * @return Form Register a new user form
	 */
	public function create(BasePresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create(self::PREFIX);
		$form->addText('username', 'username')
			->setRequired('messages.username');
		$form->addPassword('password', 'password')
			->setRequired('messages.password');
		if (!$this->presenter instanceof CreateUserPresenter &&
			$this->presenter->getUser()->isInRole('power')) {
			$form->addSelect('userType', 'userType', $this->getUserTypes());
			$form->addSelect('language', 'language', $this->getLanguages());
		}
		$form->addSubmit('add', 'add.submit');
		$form->onSuccess[] = [$this, 'add'];
		return $form;
	}

	/**
	 * Gets user types
	 * @return string[] User types
	 */
	private function getUserTypes(): array {
		$userTypes = ['normal', 'power'];
		foreach ($userTypes as $key => $type) {
			$userTypes[$type] = 'userTypes.' . $type;
			unset($userTypes[$key]);
		}
		if ($this->presenter instanceof CreateUserPresenter ||
			!$this->presenter->getUser()->isInRole('power')) {
			unset($userTypes['power']);
		}
		return $userTypes;
	}

	/**
	 * Gets languages
	 * @return string[] Available languages
	 */
	private function getLanguages(): array {
		$languages = ['en'];
		foreach ($languages as $key => $language) {
			$languages[$language] = 'languages.' . $language;
			unset($languages[$key]);
		}
		return $languages;
	}

	/**
	 * Adds a new user
	 * @param Form $form Register a new user form
	 */
	public function add(Form $form): void {
		$values = $form->getValues();
		try {
			$username = $values['username'];
			$password = $values['password'];
			$role = $values['userType'] ?? 'normal';
			$language = $values['language'] ?? 'en';
			$this->userManager->register($username, $password, $role, $language);
			$message = $form->getTranslator()->translate('messages.successAdd', ['username' => $username]);
			$this->presenter->flashMessage($message, 'success');
			if ($this->presenter instanceof UserPresenter) {
				$this->presenter->redirect('User:default');
			} else {
				$user = $this->presenter->getUser();
				$user->login($username, $password);
				$this->presenter->redirect(':Core:Homepage:default');
			}
		} catch (UsernameAlreadyExistsException $e) {
			$this->presenter->flashMessage(self::PREFIX . '.messages.usernameAlreadyExists', 'danger');
		}
	}

}
