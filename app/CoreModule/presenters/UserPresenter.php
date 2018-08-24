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

namespace App\CoreModule\Presenters;

use App\CoreModule\Forms\UserAddFormFactory;
use App\CoreModule\Forms\UserEditFormFactory;
use App\CoreModule\Model\UserManager;
use Nette\Forms\Form;

/**
 * User presenter
 */
class UserPresenter extends ProtectedPresenter {

	/**
	 * @var UserAddFormFactory Add a new user form factory
	 * @inject
	 */
	public $addFormFactory;

	/**
	 * @var UserEditFormFactory Edit an existing user form factory
	 * @inject
	 */
	public $editFormFactory;

	/**
	 * @var UserManager User manager
	 */
	private $userManager;

	/**
	 * Constructor
	 * @param UserManager $userManager User manager
	 */
	public function __construct(UserManager $userManager) {
		$this->userManager = $userManager;
		parent::__construct();
	}

	/**
	 * Render a list of users
	 */
	public function renderDefault() {
		$this->template->users = $this->userManager->getUsers();
	}

	/**
	 * Render form for editing users
	 * @param int $id User ID
	 */
	public function renderEdit(int $id) {
		$this->template->id = $id;
	}

	/**
	 * Delete an user
	 * @param int $id User ID
	 */
	public function actionDelete(int $id) {
		$user = $this->userManager->getInfo($id);
		$this->userManager->delete($id);
		if ($this->user->id === $id) {
			$this->user->logout(true);
		}
		$message = $this->translator->translate('core.user.form.messages.successDelete', ['username' => $user['username']]);
		$this->flashMessage($message, 'success');
		$this->redirect('User:default');
		$this->setView('default');
	}

	/**
	 * Create add a new user form
	 * @return Form Add a new user form
	 */
	protected function createComponentUserAddForm() {
		return $this->addFormFactory->create($this);
	}

	/**
	 * Create edit and existing user form
	 * @return Form Edit an existing user form
	 */
	protected function createComponentUserEditForm() {
		return $this->editFormFactory->create($this);
	}

}
