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

use App\CoreModule\Presenters\UserPresenter;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\UserRepository;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * User data grid
 */
class UserDataGridFactory {

	/**
	 * Translation prefix
	 */
	private const PREFIX = 'core.user';

	/**
	 * @var DataGridFactory Data grid factory
	 */
	private $dataGridFactory;

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * @var UserPresenter User manager presenter
	 */
	private $presenter;

	/**
	 * @var UserRepository User database repository
	 */
	private $repository;

	/**
	 * Constructor
	 * @param DataGridFactory $dataGridFactory Generic data grid factory
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(DataGridFactory $dataGridFactory, EntityManager $entityManager) {
		$this->dataGridFactory = $dataGridFactory;
		$this->entityManager = $entityManager;
		$this->repository = $entityManager->getUserRepository();
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
		$grid->setDataSource($this->repository->createQueryBuilder('u'));

		if ($this->presenter->getUser()->isInRole('power')) {
			$grid->addColumnNumber('id', self::PREFIX . '.id')
				->setAlign('left');
		}
		$grid->addColumnText('username', self::PREFIX . '.username');
		if ($this->presenter->getUser()->isInRole('power')) {
			$grid->addColumnStatus('role', self::PREFIX . '.userType')
				->addOption('normal', self::PREFIX . '.userTypes.normal')
				->endOption()
				->addOption('power', self::PREFIX . '.userTypes.power')
				->endOption()
				->onChange[] = [$this, 'changeRole'];
			$grid->addColumnStatus('language', self::PREFIX . '.language')
				->addOption('en', self::PREFIX . '.languages.en')
				->endOption()
				->onChange[] = [$this, 'changeLanguage'];
		}
		$grid->addAction('edit', 'config.actions.Edit')
			->setIcon('pencil')
			->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')
			->setIcon('remove')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirmation(new StringConfirmation(self::PREFIX . '.messages.confirmDelete', 'username'));
		$grid->addToolbarButton('add', 'config.actions.Add')
			->setIcon('plus')
			->setClass('btn btn-xs btn-success');
		if (!$this->presenter->getUser()->isInRole('power')) {
			$grid->allowRowsAction('edit', function (User $user): bool {
				return $user->getId() === $this->presenter->getUser()->getId();
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
		$user = $this->repository->find($id);
		assert($user instanceof User);
		$user->setRole($role);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		$this->finishChange($user);
	}

	/**
	 * Changes user's language
	 * @param string $id User ID
	 * @param string $language User's language
	 */
	public function changeLanguage(string $id, string $language): void {
		$user = $this->repository->find($id);
		assert($user instanceof User);
		$user->setLanguage($language);
		$this->finishChange($user);
	}

	/**
	 * Finishes changes in the user entity
	 * @param User $user User entity
	 */
	private function finishChange(User $user): void {
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		$loggedUser = $this->presenter->getUser();
		if ($loggedUser->getId() === $user->getId()) {
			$loggedUser->logout();
		}
		$translator = $this->presenter->getTranslator();
		$message = $translator->translate(self::PREFIX . '.messages.successEdit', null, ['username' => $user->getUserName()]);
		$this->presenter->flashSuccess($message);
		if (!$this->presenter->isAjax()) {
			$this->presenter->redirect('User:default');
		}
		if ($this->presenter->isAjax()) {
			$this->presenter['userGrid']->redrawItem($user->getId());
		}
	}

}
