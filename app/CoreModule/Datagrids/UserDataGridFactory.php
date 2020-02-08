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

namespace App\CoreModule\Datagrids;

use App\CoreModule\Exceptions\UsernameAlreadyExistsException;
use App\CoreModule\Models\UserManager;
use App\CoreModule\Presenters\UserPresenter;
use Nette\SmartObject;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * User data grid
 */
class UserDataGridFactory {

	use SmartObject;

	/**
	 * @var DataGridFactory Data grid factory
	 */
	private $dataGridFactory;

	/**
	 * @var UserManager User manager
	 */
	private $manager;

	/**
	 * @var UserPresenter User manager presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param DataGridFactory $dataGridFactory Generic data grid factory
	 * @param UserManager $manager User manager
	 */
	public function __construct(DataGridFactory $dataGridFactory, UserManager $manager) {
		$this->dataGridFactory = $dataGridFactory;
		$this->manager = $manager;
	}

	/**
	 * Creates the user data grid
	 * @param UserPresenter $presenter User presenter
	 * @param string $name Data grid's component name
	 * @return DataGrid User data grid
	 * @throws DataGridColumnStatusException
	 * @throws DataGridException
	 */
	public function create(UserPresenter $presenter, string $name): DataGrid {
		$this->presenter = $presenter;
		$grid = $this->dataGridFactory->create($presenter, $name);
		$grid->setDataSource($this->manager->getUsers());
		$grid->addColumnNumber('id', 'core.user.form.id')
			->setAlign('left');
		$grid->addColumnText('username', 'core.user.form.username');
		if ($this->presenter->getUser()->isInRole('power')) {
			$grid->addColumnStatus('role', 'core.user.form.userType')
				->addOption('normal', 'core.user.form.userTypes.normal')
				->endOption()
				->addOption('power', 'core.user.form.userTypes.power')
				->endOption()
				->onChange[] = [$this, 'changeRole'];
			$grid->addColumnStatus('language', 'core.user.form.language')
				->addOption('en', 'core.user.form.languages.en')
				->endOption()
				->onChange[] = [$this, 'changeLanguage'];
		}
		$grid->addAction('edit', 'config.actions.Edit')
			->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')
			->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirmation(new StringConfirmation('core.user.form.messages.confirmDelete', 'username'));
		$grid->addToolbarButton('add', 'config.actions.Add')
			->setIcon('plus')
			->setClass('btn btn-xs btn-success');
		if (!$this->presenter->getUser()->isInRole('power')) {
			$grid->allowRowsAction('edit', function (array $row): bool {
				return $row['id'] === $this->presenter->getUser()->getId();
			});
		}
		return $grid;
	}

	/**
	 * Changes user's role
	 * @param string $id User ID
	 * @param string $role User's role
	 */
	public function changeRole(string $id, string $role): void {
		$this->edit((int) $id, null, $role, null);
	}

	/**
	 * Changes the user
	 * @param int $id User's ID
	 * @param string|null $username Username
	 * @param string|null $role User's role
	 * @param string|null $language User's language
	 */
	private function edit(int $id, ?string $username, ?string $role, ?string $language): void {
		try {
			$this->manager->edit($id, $username, $role, $language);
			$user = $this->presenter->getUser();
			if ($user->getId() === $id) {
				$user->logout();
			}
			$translator = $this->presenter->getTranslator();
			$message = $translator->translate('core.user.form.messages.successEdit', null, ['username' => $username]);
			$this->presenter->flashSuccess($message);
			if (!$this->presenter->isAjax()) {
				$this->presenter->redirect('User:default');
			}
		} catch (UsernameAlreadyExistsException $e) {
			$this->presenter->flashError('core.user.form.messages.usernameAlreadyExists');
		} finally {
			if ($this->presenter->isAjax()) {
				$dataGrid = $this->presenter['userGrid'];
				$dataGrid->setDataSource($this->manager->getUsers());
				$dataGrid->reloadTheWholeGrid();
			}
		}
	}

	/**
	 * Changes user's language
	 * @param string $id User ID
	 * @param string $language User's language
	 */
	public function changeLanguage(string $id, string $language): void {
		$this->edit((int) $id, null, null, $language);
	}

}
