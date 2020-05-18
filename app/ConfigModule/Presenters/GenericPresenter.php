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
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Presenters\ProtectedPresenter;
use App\CoreModule\Traits\TPresenterFlashMessage;
use Nette\Application\UI\Form;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * Presenter for generic IQRF Gateway Daemon's configuration
 */
abstract class GenericPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	protected $manager;

	/**
	 * Constructor
	 * @param GenericManager $manager Generic configuration manager
	 */
	public function __construct(GenericManager $manager) {
		$this->manager = $manager;
		parent::__construct();
	}

	/**
	 * Deletes the instance
	 * @param string $component IQRF Gateway Daemon component name
	 * @param int $id Instance ID
	 * @param string|null $redirect Redirect destination
	 */
	protected function deleteInstance(string $component, int $id, ?string $redirect = null): void {
		$redirect = $redirect ?? 'Homepage:default';
		$this->manager->setComponent($component);
		try {
			$fileName = $this->manager->getFileNameById($id);
			$this->manager->deleteFile($fileName);
			$this->flashSuccess('config.messages.successes.delete');
		} catch (IOException $e) {
			$this->flashError('config.messages.deleteFailures.ioError');
		}
		$this->redirect($redirect);
	}

	/**
	 * Loads the configuration into the form
	 * @param Form $form Configuration form
	 * @param string $component IQRF Gateway Daemon component name
	 * @param int|null $id Configuration ID
	 * @param string|null $redirect Redirect destination
	 * @param callable|null $load Load configuration callback
	 */
	protected function loadFormConfiguration(Form $form, string $component, ?int $id, ?string $redirect = null, ?callable $load = null): void {
		$redirect = $redirect ?? 'Homepage:default';
		try {
			$this->manager->setComponent($component);
			if ($load === null) {
				$configuration = $this->manager->load($id ?? 0);
			} else {
				$configuration = $load($id ?? 0);
			}
			if ($id !== null && $configuration === []) {
				$this->flashError('config.messages.readFailures.notFound');
				$this->redirect($redirect);
			}
			$form->setDefaults($configuration);
		} catch (NonexistentJsonSchemaException $e) {
			$this->flashError('config.messages.readFailures.nonExistingJsonSchema');
			$this->redirect($redirect);
		} catch (IOException $e) {
			$this->flashError('config.messages.readFailures.ioError');
			$this->redirect($redirect);
		} catch (JsonException $e) {
			$this->flashError('config.messages.readFailures.invalidJson');
			$this->redirect($redirect);
		}
	}

}
