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
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use App\IqrfNetModule\Models\ApiSchemaManager;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use Contributte\FormMultiplier\Multiplier;
use Contributte\Translation\Wrappers\NotTranslate;
use DateTime;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextArea;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use stdClass;
use Throwable;

/**
 * Scheduler's task configuration form factory
 */
class SchedulerFormFactory {

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
	 * @var ApiSchemaManager JSON API schema manager
	 */
	private $schemaManager;

	/**
	 * @var ServiceManager Service manager
	 */
	private $serviceManager;

	/**
	 * Translation prefix
	 */
	private const PREFIX = 'config.scheduler.form.';

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param SchedulerManager $manager Scheduler manager
	 * @param ServiceManager $serviceManager Service manager
	 * @param ApiSchemaManager $schemaManager JSON API schema manager
	 */
	public function __construct(FormFactory $factory, SchedulerManager $manager, ServiceManager $serviceManager, ApiSchemaManager $schemaManager) {
		$this->manager = $manager;
		$this->factory = $factory;
		$this->serviceManager = $serviceManager;
		$this->schemaManager = $schemaManager;
	}

	/**
	 * Creates the Scheduler's task configuration form
	 * @param SchedulerPresenter $presenter Scheduler configuration presenter
	 * @return Form Scheduler's task configuration form
	 */
	public function create(SchedulerPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		try {
			$services = $this->manager->getServices();
		} catch (JsonException $e) {
			$services = [];
		}
		$form->addGroup();
		$taskId = $form->addInteger('taskId', self::PREFIX . 'taskId');
		try {
			$now = new DateTime();
			$taskId->setDefaultValue($now->getTimestamp());
		} catch (Throwable $e) {
			// Do nothing
		}
		$form->addSelect('clientId', self::PREFIX . 'clientId')
			->setItems($services, false)
			->setPrompt(self::PREFIX . 'messages.clientId-prompt')
			->setRequired(self::PREFIX . 'messages.clientId')
			->checkDefaultValue(false);
		$this->addTimeSpec($form);
		$form->addGroup(self::PREFIX . 'message.title');
		/**
		 * @var Multiplier $tasks
		 */
		$tasks = $form->addMultiplier('task', [$this, 'createTasksMultiplier'], 1);
		$tasks->addCreateButton(self::PREFIX . 'message.add')
			->addClass('btn btn-success');
		$tasks->addRemoveButton(self::PREFIX . 'message.remove')
			->addClass('btn btn-danger');
		$form->addGroup();
		$form->addSubmit('save', self::PREFIX . 'save')
			->setHtmlAttribute('class', 'btn btn-primary');
		$form->addSubmit('saveAndRestart', self::PREFIX . 'saveAndRestart')
			->setHtmlAttribute('class', 'btn btn-primary');
		$form->onValidate[] = [$this, 'validate'];
		$form->onSubmit[] = [$this, 'save'];
		$id = $presenter->getParameter('id');
		if (isset($id)) {
			$form->setDefaults($this->load((int) $id));
		}
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

	/**
	 * Creates tasks multiplier
	 * @param Container $container Form container
	 * @throws JsonException
	 */
	public function createTasksMultiplier(Container $container): void {
		$container->addSelect('messaging', self::PREFIX . 'messaging')
			->setItems($this->getMessagings())
			->setPrompt(self::PREFIX . 'messages.messaging-prompt')
			->setRequired(self::PREFIX . 'messages.messaging')
			->checkDefaultValue(false);
		$container->addTextArea('message', self::PREFIX . 'message.label')
			->setRequired(self::PREFIX . 'messages.message');
	}

	/**
	 * Loads the task
	 * @param int $id Task ID
	 * @return stdClass Task
	 */
	private function load(int $id): stdClass {
		try {
			$configuration = $this->manager->load($id);
			foreach ($configuration->task as &$task) {
				$task->message = Json::encode($task->message, Json::PRETTY);
			}
			return $configuration;
		} catch (InvalidJsonException | IOException | JsonException $e) {
			return new stdClass();
		}
	}

	/**
	 * Adds the time specification
	 * @param Form $form Task's configuration form
	 */
	private function addTimeSpec(Form $form): void {
		$timeSpec = $form->addContainer('timeSpec');
		$timeSpec->addText('cronTime', self::PREFIX . 'timeSpec.cronTime');
		$timeSpec->addCheckbox('exactTime', self::PREFIX . 'timeSpec.exactTime');
		$timeSpec->addCheckbox('periodic', self::PREFIX . 'timeSpec.periodic');
		$timeSpec->addInteger('period', self::PREFIX . 'timeSpec.period')
			->setDefaultValue(0);
		$timeSpec->addText('startTime', self::PREFIX . 'timeSpec.startTime')
			->setHtmlType('datetime-local');
		$timeSpec['period']
			->addConditionOn($timeSpec['periodic'], Form::EQUAL, true)
			->setRequired(self::PREFIX . 'messages.timeSpec.period');
		$timeSpec['startTime']
			->addConditionOn($timeSpec['exactTime'], Form::EQUAL, true)
			->setRequired(self::PREFIX . 'messages.timeSpec.startTime');
	}

	/**
	 * Returns available messagings
	 * @return array<string,NotTranslate> Messagings
	 * @throws JsonException
	 */
	private function getMessagings(): array {
		$data = [];
		foreach ($this->manager->getMessagings() as $messagings) {
			foreach ($messagings as $messaging) {
				$data[$messaging] = new NotTranslate($messaging);
			}
		}
		return $data;
	}

	/**
	 * Validates scheduler task configuration from values
	 * @param Form $form Scheduler task configuration from
	 */
	public function validate(Form $form): void {
		/**
		 * @var Multiplier $tasks
		 */
		$tasks = $form['task'];
		foreach ($tasks->getContainers() as $task) {
			/**
			 * @var TextArea $message
			 */
			$message = $task->getComponent('message');
			$value = $message->getValue();
			if ($value === '') {
				continue;
			}
			try {
				$json = Json::decode($value);
				$this->schemaManager->setSchemaForRequest($json->mType ?? 'unknown');
				$this->schemaManager->validate($json);
			} catch (JsonException $e) {
				$message->addError(self::PREFIX . 'messages.messageInvalidJson');
			} catch (NonexistentJsonSchemaException $e) {
				$message->addError(new NotTranslate($e->getMessage()));
			} catch (InvalidJsonException $e) {
				$message->addError(new NotTranslate($e->getMessage()));
			}
		}
	}

	/**
	 * Saves the task's configuration
	 * @param Form $form Scheduler task configuration form
	 */
	public function save(Form $form): void {
		try {
			$values = $form->getValues();
			foreach ($values->task as &$task) {
				$task->message = Json::decode($task->message);
			}
			$values->task = (array) $values->task;
			$this->manager->save($values);
			$this->presenter->flashSuccess('config.messages.success');
			/**
			 * @var SubmitButton $restartButton Save and restart submit button
			 */
			$restartButton = $form['saveAndRestart'];
			if ($restartButton->isSubmittedBy()) {
				$this->serviceManager->restart();
				$this->presenter->flashInfo('service.iqrf-gateway-daemon.messages.restart');
			}
			$this->presenter->redirect('Scheduler:default');
		} catch (NonexistentJsonSchemaException $e) {
			$this->presenter->flashError($e->getMessage());
		} catch (InvalidJsonException $e) {
			$this->presenter->flashError($e->getMessage());
		} catch (IOException $e) {
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} catch (JsonException $e) {
			$this->presenter->flashError('config.messages.writeFailures.invalidJson');
		} catch (UnsupportedInitSystemException $e) {
			$this->presenter->flashError('service.errors.unsupportedInit');
		}
	}

}
