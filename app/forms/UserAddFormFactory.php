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
use App\Presenters\UserPresenter;
use App\Model\UserManager;
use App\Model\UsernameAlreadyExists;
use Nette;
use Nette\Forms\Form;

/**
 * Register a new user form factory.
 */
class UserAddFormFactory {

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
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param UserManager $userManager User manager
	 */
	public function __construct(FormFactory $factory, UserManager $userManager) {
		$this->factory = $factory;
		$this->userManager = $userManager;
	}

	/**
	 * Create register a new user form
	 * @param UserPresenter User manager presenter
	 * @return Form Register a new user form
	 */
	public function create(UserPresenter $presenter): Form {
		$userTypes = [
			'normal' => 'userTypes.normal',
			'power' => 'userTypes.power'
		];
		$languages = [
			'en' => 'languages.en',
		];
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('core.user.form'));
		$form->addText('username', 'username')->setRequired('messages.username');
		$form->addPassword('password', 'password')->setRequired('messages.password');
		$form->addSelect('userType', 'userType', $userTypes);
		$form->addSelect('language', 'language', $languages);
		$form->addSubmit('add', 'add');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			try {
				$this->userManager->register($values['username'], $values['password'], $values['userType'], $values['language']);
				$message = $form->getTranslator()->translate('messages.successAdd', ['username' => $values['username']]);
				$presenter->flashMessage($message, 'success');
				$presenter->redirect('User:default');
			} catch (\Exception $e) {
				if ($e instanceof UsernameAlreadyExists) {
					$presenter->flashMessage('core.user.form.messages.usernameAlreadyExists', 'danger');
				} else {
					throw $e;
				}
			}
		};
		return $form;
	}

}
