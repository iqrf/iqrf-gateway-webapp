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

use App\ConfigModule\Models\GenericManager;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use App\CoreModule\Presenters\ProtectedPresenter;
use App\CoreModule\Traits\TPresenterFlashMessage;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * Presenter for generic IQRF Gateway Daemon's configuration
 */
abstract class GenericPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

	/**
	 * @var string[] IQRF Gateway Daemon's components
	 */
	protected $components;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	protected $configManager;

	/**
	 * Constructor
	 * @param string[] $components IQRF Gateway Daemon's components
	 * @param GenericManager $manager Generic configuration manager
	 */
	public function __construct(array $components, GenericManager $manager) {
		$this->components = $components;
		$this->configManager = $manager;
		parent::__construct();
	}

	/**
	 * Checks if the files are readable
	 */
	protected function startup(): void {
		parent::startup();
		try {
			$this->checkInstanceFiles();
		} catch (NonExistingJsonSchemaException $e) {
			$this->flashError('config.messages.readFailures.nonExistingJsonSchema');
			$this->redirect('Homepage:default');
		} catch (IOException $e) {
			$this->flashError('config.messages.readFailures.ioError');
			$this->redirect('Homepage:default');
		} catch (JsonException $e) {
			$this->flashError('config.messages.readFailures.invalidJson');
			$this->redirect('Homepage:default');
		}
	}

	/**
	 * Checks the component's instance files
	 * @throws JsonException
	 */
	private function checkInstanceFiles(): void {
		foreach ($this->components as $component) {
			$this->configManager->setComponent($component);
			$files = array_keys($this->configManager->getInstanceFiles());
			foreach ($files as $id) {
				$this->configManager->load($id);
			}
		}
	}

}
