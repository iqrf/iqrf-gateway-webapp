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

use App\CoreModule\Presenters\UserPresenter;
use App\CoreModule\Model\UserManager;
use Nette;
use Ublaboo\DataGrid\DataGrid;

/**
 * Render an user datagrid
 */
class UserDataGridFactory {

	use Nette\SmartObject;

	/**
	 * @var DataGridFactory Data grid factory
	 */
	private $datagridFactory;

	/**
	 * @var UserManager User manager
	 */
	private $userManager;

	/**
	 * Constructor
	 * @param DataGridFactory $datagridFactory Generic datagrid factory
	 * @param UserManager $userManager User manager
	 */
	public function __construct(DataGridFactory $datagridFactory, UserManager $userManager) {
		$this->datagridFactory = $datagridFactory;
		$this->userManager = $userManager;
	}

	/**
	 * Create user datagrid
	 * @param UserPresenter $presenter User presenter
	 * @param string Datagrid's component name
	 * @return DataGrid User datagrid
	 */
	public function create(UserPresenter $presenter, string $name): DataGrid {
		$grid = $this->datagridFactory->create($presenter, $name);
		$grid->setDataSource($this->userManager->getUsers());
		$grid->addColumnNumber('id', 'core.user.form.id')->setAlign('left');
		$grid->addColumnText('username', 'core.user.form.username');
		$grid->addColumnText('role', 'core.user.form.userType');
		$grid->addColumnText('language', 'core.user.form.language');
		$grid->addAction('edit', 'config.actions.Edit')->setIcon('pencil')
				->setClass('btn btn-xs btn-info');
		$grid->addAction('delete', 'config.actions.Remove')->setIcon('remove')
				->setClass('btn btn-xs btn-danger ajax')
				->setConfirm('core.user.form.messages.confirmDelete', 'username');
		$grid->addToolbarButton('add', 'config.actions.Add')
				->setClass('btn btn-xs btn-success');
		return $grid;
	}

}
