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

use App\ConfigModule\Models\SchedulerManager;
use App\ConfigModule\Presenters\SchedulerPresenter;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Scheduler's task import form factory
 */
class SchedulerImportFormFactory {

	use SmartObject;

	/**
	 * @var SchedulerManager Scheduler manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var SchedulerPresenter Scheduler presenter
	 */
	private $presenter;

	/**
	 * Translator prefix
	 */
	private const TRANSLATOR_PREFIX = 'config.scheduler.importForm';

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param SchedulerManager $manager Scheduler manager
	 */
	public function __construct(FormFactory $factory, SchedulerManager $manager) {
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Creates the task import form
	 * @param SchedulerPresenter $presenter Scheduler presenter
	 * @return Form Task import form
	 */
	public function create(SchedulerPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create(self::TRANSLATOR_PREFIX);
		$form->addUpload('file', 'file')
			->setRequired('messages.file')
			->setHtmlAttribute('accept', 'application/json');
		$form->addSubmit('import', 'import');
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'import'];
		return $form;
	}

	public function import(Form $form): void {
		$values = $form->getValues('array');
		/**
		 * @var FileUpload $file JSON file
		 */
		$file = $values['file'];
		try {
			$json = Json::decode($file->getContents(), Json::FORCE_ARRAY);
		} catch (JsonException $e) {
			$form['file']->addError('messages.invalidFile');
			$this->presenter->flashError(self::TRANSLATOR_PREFIX . '.messages.invalidFile');
			return;
		}
		try {
			$this->manager->save($json);
			$this->presenter->flashSuccess(self::TRANSLATOR_PREFIX . '.messages.success');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashError('config.messages.writeFailures.nonExistingJsonSchema');
		} catch (IOException $e) {
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} catch (JsonException $e) {
			$this->presenter->flashError('config.messages.writeFailures.invalidJson');
		}
		$this->presenter->redirect('Scheduler:default');
	}

}
