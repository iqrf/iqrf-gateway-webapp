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
use App\Model\UserManager;
use Nette;
use Nette\Forms\Form;
use Nette\Security\User;

/**
 * Change user type form factory.
 */
class ChangeUserTypeFormFactory {

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
	 * Create change user type form
	 * @param ProfilePresenter Profile presenter
	 * @return Form Change user type form
	 */
	public function create(ProfilePresenter $presenter): Form {
		$userTypes = [
			'normal' => 'userTypes.normal',
			'power' => 'userTypes.power'
		];
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('core.changeUserType.form'));
		$form->addSelect('userType', 'userType', $userTypes)->setRequired('messages.userType');
		$form->addSubmit('change', 'change');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			$userId = $this->user->getId();
			$this->userManager->changeUserType($userId, $values['userType']);
			$this->user->logout();
			$presenter->flashMessage('core.changeUserType.form.messages.success', 'success');
			$presenter->redirect('Sign:in');
		};
		return $form;
	}

}
