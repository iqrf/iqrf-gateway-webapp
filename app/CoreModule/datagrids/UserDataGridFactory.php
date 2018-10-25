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

namespace App\CoreModule\Datagrids;

use App\CoreModule\Exceptions\UsernameAlreadyExistsException;
use App\CoreModule\Models\UserManager;
use App\CoreModule\Presenters\UserPresenter;
use Kdyby\Translation\Phrase;
use Nette\SmartObject;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Render an user data grid
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
	 * Create user data grid
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
		$grid->addColumnNumber('id', 'core.user.form.id')->setAlign('left');
		$grid->addColumnText('username', 'core.user.form.username');
		$grid->addColumnStatus('role', 'core.user.form.userType')
			->addOption('normal', 'core.user.form.userTypes.normal')->endOption()
			->addOption('power', 'core.user.form.userTypes.power')->endOption()
			->onChange[] = [$this, 'changeRole'];
		$grid->addColumnStatus('language', 'core.user.form.language')
			->addOption('en', 'core.user.form.languages.en')->endOption()
			->onChange[] = [$this, 'changeLanguage'];
		$grid->addAction('edit', 'config.actions.Edit')->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirm('core.user.form.messages.confirmDelete', 'username');
		$grid->addToolbarButton('add', 'config.actions.Add')
			->setClass('btn btn-xs btn-success');
		return $grid;
	}

	/**
	 * Change user's role
	 * @param int $id User ID
	 * @param string $role User's role
	 */
	public function changeRole(int $id, string $role): void {
		$user = $this->manager->getInfo($id);
		try {
			$this->manager->edit($id, $user['username'], $role, $user['language']);
			if ($this->presenter->user->id === $id) {
				$this->presenter->user->logout();
			}
			$message = new Phrase('core.user.form.messages.successEdit', null,['username' => $user['username']]);
			$this->presenter->flashMessage($message, 'success');
			$this->presenter->redirect('User:default');
		} catch (UsernameAlreadyExistsException $e) {
			$this->presenter->flashMessage('core.user.form.messages.usernameAlreadyExists', 'danger');
		} finally {
			if ($this->presenter->isAjax()) {
				$this->presenter->redrawControl('flashes');
				$dataGrid = $this->presenter['userGrid'];
				$dataGrid->setDataSource($this->manager->getUsers());
				$dataGrid->redrawItem($id);
			}
		}
	}

	/**
	 * Change user's language
	 * @param int $id User ID
	 * @param string $language User's language
	 */
	public function changeLanguage(int $id, string $language): void {
		$user = $this->manager->getInfo($id);
		try {
			$this->manager->edit($id, $user['username'], $user['role'], $language);
			if ($this->presenter->user->id === $id) {
				$this->presenter->user->logout();
			}
			$message = new Phrase('core.user.form.messages.successEdit', null,['username' => $user['username']]);
			$this->presenter->flashMessage($message, 'success');
			$this->presenter->redirect('User:default');
		} catch (UsernameAlreadyExistsException $e) {
			$this->presenter->flashMessage('core.user.form.messages.usernameAlreadyExists', 'danger');
		} finally {
			if ($this->presenter->isAjax()) {
				$this->presenter->redrawControl('flashes');
				$dataGrid = $this->presenter['userGrid'];
				$dataGrid->setDataSource($this->manager->getUsers());
				$dataGrid->redrawItem($id);
			}
		}
	}

}
