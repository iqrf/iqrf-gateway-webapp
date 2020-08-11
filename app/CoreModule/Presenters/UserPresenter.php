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

use App\CoreModule\Datagrids\UserDataGridFactory;
use App\CoreModule\Forms\UserAddFormFactory;
use App\CoreModule\Forms\UserEditFormFactory;
use App\CoreModule\Traits\TPresenterFlashMessage;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * User presenter
 */
class UserPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

	/**
	 * @var UserAddFormFactory Add a new user form factory
	 * @inject
	 */
	public $addFormFactory;

	/**
	 * @var UserDataGridFactory User data grid factory
	 * @inject
	 */
	public $dataGridFactory;

	/**
	 * @var UserEditFormFactory Edit an existing user form factory
	 * @inject
	 */
	public $editFormFactory;

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(EntityManager $entityManager) {
		$this->entityManager = $entityManager;
		parent::__construct();
	}

	/**
	 * Renders the form for editing users
	 * @param int $id User ID
	 */
	public function actionEdit(int $id): void {
		$user = $this->entityManager->getUserRepository()->find($id);
		if ($user === null) {
			$this->flashError('core.user.messages.notFound');
			$this->redirect('User:default');
		}
		assert($user instanceof User);
		$this['userEditForm']->setDefaults($user->jsonSerialize());
	}

	/**
	 * Deletes an user
	 * @param int $id User ID
	 */
	public function actionDelete(int $id): void {
		$user = $this->entityManager->getUserRepository()->find($id);
		if ($user === null) {
			$this->flashError('core.user.messages.notFound');
			$this->redirect('User:default');
		}
		assert($user instanceof User);
		$this->entityManager->remove($user);
		$this->entityManager->flush();
		if ($this->getUser()->getId() === $id) {
			$this->getUser()->logout(true);
		}
		$message = $this->translator->translate('core.user.messages.successDelete', ['username' => $user->getUserName()]);
		$this->flashSuccess($message);
		$this->redirect('User:default');
	}

	/**
	 * Creates the add a new user form
	 * @return Form Add a new user form
	 */
	protected function createComponentUserAddForm(): Form {
		return $this->addFormFactory->create($this);
	}

	/**
	 * Creates the data grid
	 * @param string $name Component name
	 * @return DataGrid Datagrid with users
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 */
	protected function createComponentUserGrid(string $name): DataGrid {
		return $this->dataGridFactory->create($this, $name);
	}

	/**
	 * Creates the edit an existing user form
	 * @return Form Edit an existing user form
	 */
	protected function createComponentUserEditForm(): Form {
		return $this->editFormFactory->create($this);
	}

}
