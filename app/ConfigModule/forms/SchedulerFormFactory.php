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

namespace App\ConfigModule\Forms;

use App\ConfigModule\Model\SchedulerManager;
use App\ConfigModule\Presenters\SchedulerPresenter;
use App\CoreModule\Exception\NonExistingJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use Nette;
use Nette\Forms\Form;
use Nette\IOException;

/**
 * Scheduler's task configuration form factory
 */
class SchedulerFormFactory {

	use Nette\SmartObject;

	/**
	 * @var SchedulerManager Scheduler manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var int Scheduler's task ID
	 */
	private $id;

	/**
	 * @var SchedulerPresenter Scheduler presenter
	 */
	private $presenter;

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
	 * Create Scheduler's task configuration form
	 * @param SchedulerPresenter $presenter Scheduler presenter
	 * @return Form Scheduler's task configuration form
	 */
	public function create(SchedulerPresenter $presenter): Form {
		$this->presenter = $presenter;
		$this->id = intval($presenter->getParameter('id'));
		$form = $this->factory->create();
		$translator = $form->getTranslator();
		$form->setTranslator($translator->domain('config.scheduler.form'));
		$defaults = $this->manager->load($this->id);
		$cTypes = [
			'dpa' => 'cTypes.dpa',
		];
		$types = [
			'raw', 'raw-hdp',
		];
		foreach ($types as $key => $type) {
			unset($types[$key]);
			$types[$type] = 'types.' . $type;
		}
		$form->addText('time', 'time');
		$form->addSelect('service', 'config.scheduler.form.service')
				->setItems($this->manager->getServices(), false)
				->setTranslator($translator)
				->setPrompt('config.scheduler.form.messages.service-prompt')
				->setRequired('messages.service')->checkDefaultValue(false);
		$task = $form->addContainer('task');
		$task->addSelect('messaging', 'config.scheduler.form.messaging')
				->setItems($this->manager->getMessagings(), false)
				->setTranslator($translator)
				->setPrompt('config.scheduler.form.messages.messaging-prompt')
				->setRequired('messages.messaging')->checkDefaultValue(false);
		$message = $task->addContainer('message');
		$message->addSelect('ctype', 'ctype', $cTypes)
				->setPrompt('messages.ctype-prompt')
				->setRequired('messages.ctype')->checkDefaultValue(false);
		$message->addSelect('type', 'type', $types)
				->setPrompt('messages.type-prompt')
				->setRequired('messages.type')->checkDefaultValue(false);
		switch ($defaults['task']['message']['type']) {
			case 'raw-hdp':
				$message->addText('nadr', 'nadr')->setRequired('messages.nadr')
						->addRule(Form::PATTERN, 'messages.nadr-rule', '[0-9A-Fa-f]{1,2}')
						->addRule(Form::MAX_LENGTH, 'messages.nadr-rule', 2);
				$message->addText('pnum', 'pnum')->setRequired('messages.pnum')
						->addRule(Form::PATTERN, 'messages.pnum-rule', '[0-9A-Fa-f]{1,2}')
						->addRule(Form::MAX_LENGTH, 'messages.pnum-rule', 2);
				$message->addText('pcmd', 'pcmd')->setRequired('messages.pcmd')
						->addRule(Form::PATTERN, 'messages.pcmd-rule', '[0-9A-Fa-f]{1,2}')
						->addRule(Form::MAX_LENGTH, 'messages.pcmd-rule', 2);
				$message->addText('hwpid', 'hwpid')->setRequired(false)
						->addRule(Form::PATTERN, 'messages.hwpid-rule', '[0-9A-Fa-f]{4}')
						->addRule(Form::MAX_LENGTH, 'messages.hwpid-rule', 4);
				$message->addText('rcode', 'rcode');
				$message->addText('rdata', 'rdata');
				break;
			default:
				break;
		}
		$message->addInteger('timeout', 'timeout');
		$message->addText('request', 'request');
		$message->addText('request_ts', 'request_ts');
		$message->addText('confirmation', 'confirmation');
		$message->addText('confirmation_ts', 'confirmation_ts');
		$message->addText('response', 'response');
		$message->addText('response_ts', 'response_ts');
		$form->addSubmit('save', 'Save');
		$form->setDefaults($defaults);
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Save scheduler's task configuration
	 * @param Form $form Scheduler's task configuration fore
	 */
	public function save(Form $form): void {
		try {
			$this->manager->save($form->getValues(true), $this->id);
			$this->presenter->flashMessage('config.messages.success', 'success');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.nonExistingJsonSchema', 'danger');
		} catch (IOException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.ioError', 'danger');
		} finally {
			$this->presenter->redirect('Scheduler:default');
		}
	}

}
