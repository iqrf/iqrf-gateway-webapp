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

namespace App\ConfigModule\Forms;

use App\ConfigModule\Models\GenericManager;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use App\CoreModule\Presenters\ProtectedPresenter;
use Nette\Application\UI\Form;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * Generic configuration form factory
 */
class GenericConfigFormFactory {

	use SmartObject;

	/**
	 * @var GenericManager Generic config manager
	 */
	protected $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	protected $factory;

	/**
	 * @var ProtectedPresenter Generic protected  presenter
	 */
	protected $presenter;

	/**
	 * @var string The presenter to redirect after save
	 */
	protected $redirect = 'Homepage:default';

	/**
	 * Constructor
	 * @param GenericManager $manager Generic configuration manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(GenericManager $manager, FormFactory $factory) {
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Saves the generic configuration
	 * @param Form $form Configuration form
	 * @throws JsonException
	 */
	public function save(Form $form): void {
		try {
			$this->manager->save($form->getValues('array'));
			$this->presenter->flashSuccess('config.messages.success');
		} catch (NonexistentJsonSchemaException $e) {
			$this->presenter->flashError('config.messages.writeFailures.nonExistingJsonSchema');
		} catch (IOException $e) {
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} finally {
			$this->presenter->redirect($this->redirect);
		}
	}

}
