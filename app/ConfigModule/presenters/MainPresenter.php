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

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Forms\MainFormFactory;
use App\ConfigModule\Models\MainManager;
use App\CoreModule\Presenters\ProtectedPresenter;
use App\CoreModule\Traits\TPresenterFlashMessage;
use Nette\Forms\Form;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * Main daemon configuration presenter
 */
class MainPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

	/**
	 * @var MainFormFactory Main daemon's configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var MainManager Main configuration manager
	 */
	private $configManager;

	/**
	 * Constructor
	 * @param MainManager $configManager Main configuration manager
	 */
	public function __construct(MainManager $configManager) {
		$this->configManager = $configManager;
		parent::__construct();
	}

	/**
	 * Catches exceptions
	 */
	public function actionDefault(): void {
		try {
			$this->configManager->load();
		} catch (IOException $e) {
			$this->flashError('config.messages.readFailures.ioError');
			$this->redirect('Homepage:default');
		} catch (JsonException $e) {
			$this->flashError('config.messages.readFailures.invalidJson');
			$this->redirect('Homepage:default');
		}
	}

	/**
	 * Creates the Main daemon's configuration form
	 * @return Form Main daemon's configuration form
	 * @throws JsonException
	 */
	protected function createComponentConfigMainForm(): Form {
		return $this->formFactory->create($this);
	}

}
