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
declare(strict_types=1);

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Model\GenericManager;
use App\CoreModule\Exception\NonExistingJsonSchemaException;
use App\CoreModule\Presenters\ProtectedPresenter;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * Presenter for generic IQRF Gateway Daemon's configuration presenters
 */
abstract class GenericPresenter extends ProtectedPresenter {

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
	 * Catch exceptions
	 */
	public function actionDefault(): void {
		try {
			$this->checkInstanceFiles();
		} catch (NonExistingJsonSchemaException $e) {
			$this->flashMessage('config.messages.nonExistingJsonSchema', 'danger');
			$this->redirect('Homepage:default');
		} catch (IOException $e) {
			$this->flashMessage('config.messages.readFailure', 'danger');
			$this->redirect('Homepage:default');
		} catch (JsonException $e) {
			$this->flashMessage('config.messages.invalidJson', 'danger');
			$this->redirect('Homepage:default');
		}
	}

	/**
	 * Check component's instance files
	 */
	private function checkInstanceFiles(): void {
		foreach ($this->components as $component) {
			$this->configManager->setComponent($component);
			$files = $this->configManager->getInstanceFiles();
			foreach ($files as $file) {
				$this->configManager->setFileName($file);
				$this->configManager->load();
			}
		}
	}

}
