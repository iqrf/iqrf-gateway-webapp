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
use App\ServiceModule\Exceptions\NotSupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\JsonException;

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
	 * @var string[] Message types
	 */
	private $mTypes = [
		'iqrfRaw' => 'mTypes.iqrfRaw',
		'iqrfRawHdp' => 'mTypes.iqrfRawHdp',
	];

	/**
	 * @var ServiceManager Service manager
	 */
	private $serviceManager;

	/**
	 * @var mixed[] Task's settings
	 */
	private $task;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param SchedulerManager $manager Scheduler manager
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(FormFactory $factory, SchedulerManager $manager, ServiceManager $serviceManager) {
		$this->manager = $manager;
		$this->factory = $factory;
		$this->serviceManager = $serviceManager;
	}

	/**
	 * Creates the Scheduler's task configuration form
	 * @param SchedulerPresenter $presenter Scheduler configuration presenter
	 * @return Form Scheduler's task configuration form
	 */
	public function create(SchedulerPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('config.scheduler.form');
		$translator = $this->factory->getTranslator();
		try {
			$this->load($presenter->getParameters());
			$messagings = $this->manager->getMessagings();
			$services = $this->manager->getServices();
		} catch (JsonException $e) {
			$messagings = [];
			$services = [];
		}
		$form->addInteger('taskId', 'taskId');
		$form->addSelect('clientId', 'config.scheduler.form.clientId')
			->setItems($services, false)
			->setTranslator($translator)
			->setPrompt('config.scheduler.form.messages.clientId-prompt')
			->setRequired('messages.clientId')
			->checkDefaultValue(false);
		$this->addTimeSpec($form);
		$task = $form->addContainer('task');
		$task->addSelect('messaging', 'config.scheduler.form.messaging')
			->setItems($messagings, false)
			->setTranslator($translator)
			->setPrompt('config.scheduler.form.messages.messaging-prompt')
			->setRequired('messages.messaging')
			->checkDefaultValue(false);
		$this->addMessage($task);
		$form->addSubmit('save', 'save')->onClick[] = [$this, 'save'];
		$form->addSubmit('saveAndRestart', 'saveAndRestart')->onClick[] = [$this, 'saveAndRestart'];
		$form->setDefaults($this->task);
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

	/**
	 * Adds the message
	 * @param Container $task Task container
	 */
	private function addMessage(Container $task): void {
		$message = $task->addContainer('message');
		$message->addSelect('mType', 'mType', $this->mTypes)
			->setPrompt('messages.mType-prompt')
			->setRequired('messages.mType')
			->checkDefaultValue(false);
		$data = $message->addContainer('data');
		$data->addText('msgId', 'msgId');
		$data->addInteger('timeout', 'timeout');
		$req = $data->addContainer('req');
		switch ($this->task['task']['message']['mType']) {
			case 'iqrfRaw':
				$this->addRaw($req);
				break;
			case 'iqrfRawHdp':
				$pData = &$this->task['task']['message']['data']['req']['pData'];
				$pData = implode('.', $pData);
				$this->addRawHdp($req);
				break;
		}
		$data->addCheckbox('returnVerbose', 'returnVerbose');
	}

	/**
	 * Adds the time specification
	 * @param Form $form Task's configuration form
	 */
	private function addTimeSpec(Form $form): void {
		$timeSpec = $form->addContainer('timeSpec');
		$timeSpec->addText('cronTime', 'timeSpec.cronTime');
		$timeSpec->addCheckbox('exactTime', 'timeSpec.exactTime');
		$timeSpec->addCheckbox('periodic', 'timeSpec.periodic');
		$timeSpec->addInteger('period', 'timeSpec.period');
		$timeSpec->addText('startTime', 'timeSpec.startTime')
			->setHtmlType('datetime-local');
		$timeSpec['period']
			->addConditionOn($timeSpec['periodic'], Form::EQUAL, true)
			->setRequired('messages.timeSpec.period');
		$timeSpec['startTime']
			->addConditionOn($timeSpec['exactTime'], Form::EQUAL, true)
			->setRequired('messages.timeSpec.startTime');
	}

	/**
	 * Adds the inputs for DPA raw request
	 * @param Container $req Form request container
	 */
	private function addRaw(Container $req): void {
		$req->addText('rData', 'request');
	}

	/**
	 * Adds the inputs for DPA raw HDP request
	 * @param Container $req Form request container
	 */
	private function addRawHdp(Container $req): void {
		$req->addInteger('nAdr', 'nAdr');
		$req->addInteger('pNum', 'pNum');
		$req->addInteger('pCmd', 'pCmd');
		$req->addInteger('hwpId', 'hwpId');
		$req->addText('pData', 'pData');
	}

	/**
	 * Loads the task's configuration
	 * @param mixed[] $parameters Presenter's parameters
	 * @throws JsonException
	 */
	private function load(array $parameters): void {
		if (array_key_exists('id', $parameters)) {
			$this->task = $this->manager->load(intval($parameters['id']));
		} elseif (array_key_exists('type', $parameters)) {
			$this->task = $this->manager->loadType($parameters['type']);
		}
	}

	/**
	 * Saves the task's configuration
	 * @param SubmitButton $button Submit button
	 */
	public function save(SubmitButton $button): void {
		try {
			$values = $button->getForm()->getValues(true);
			$this->manager->save($values);
			$this->presenter->flashMessage('config.messages.success', 'success');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.nonExistingJsonSchema', 'danger');
		} catch (IOException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.ioError', 'danger');
		} catch (JsonException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.invalidJson', 'danger');
		} finally {
			$this->presenter->redirect('Scheduler:default');
		}
	}

	/**
	 * Saves the task's configuration and restart IQRF Gateway Daemon
	 * @param SubmitButton $button Submit button
	 */
	public function saveAndRestart(SubmitButton $button): void {
		try {
			$this->serviceManager->restart();
			$this->presenter->flashMessage('service.actions.restart.message', 'info');
		} catch (NotSupportedInitSystemException $e) {
			$this->presenter->flashMessage('service.errors.unsupportedInit', 'danger');
		} finally {
			$this->save($button);
		}
	}

}
