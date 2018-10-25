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

namespace App\ConfigModule\Forms;

use App\ConfigModule\Models\SchedulerManager;
use App\ConfigModule\Presenters\SchedulerPresenter;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use Nette\Forms\Container;
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
	 * @var int Scheduler's task ID
	 */
	private $id;

	/**
	 * @var SchedulerPresenter Scheduler presenter
	 */
	private $presenter;

	/**
	 * @var array Message types
	 */
	private $mTypes = [
		'iqrfRaw'=> 'mTypes.iqrfRaw',
		'iqrfRawHdp'=> 'mTypes.iqrfRawHdp',
	];

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
	 * @throws JsonException
	 */
	public function create(SchedulerPresenter $presenter): Form {
		$this->presenter = $presenter;
		$this->id = intval($presenter->getParameter('id'));
		$form = $this->factory->create();
		$translator = $form->getTranslator();
		$form->setTranslator($translator->domain('config.scheduler.form'));
		$defaults = $this->manager->load($this->id);
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
		$message->addSelect('mType', 'mType', $this->mTypes)
			->setPrompt('messages.mType-prompt')
			->setRequired('messages.mType')->checkDefaultValue(false);
		$data = $message->addContainer('data');
		$data->addText('msgId', 'msgId');
		$data->addInteger('timeout', 'timeout');
		$req = $data->addContainer('req');
		switch ($defaults['task']['message']['mType']) {
			case 'iqrfRaw':
				$this->addRaw($req);
				break;
			case 'iqrfRawHdp':
				$pData = &$defaults['task']['message']['data']['req']['pData'];
				$pData = implode('.', $pData);
				$this->addRawHdp($req);
				break;
		}
		$data->addCheckbox('returnVerbose', 'returnVerbose');
		$form->addSubmit('save', 'Save');
		$form->setDefaults($defaults);
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Add inputs for DPA raw request
	 * @param Container $req Form request container
	 */
	private function addRaw(Container $req):void {
		$req->addText('rData', 'request');
	}

	/**
	 * Add inputs for DPA raw HDP request
	 * @param Container $req Form request container
	 */
	private function addRawHdp(Container $req):void {
		$req->addInteger('nAdr', 'nAdr');
		$req->addInteger('pNum', 'pNum');
		$req->addInteger('pCmd', 'pCmd');
		$req->addInteger('hwpId', 'hwpId');
		$req->addText('pData', 'pData');
	}

	/**
	 * Save scheduler's task configuration
	 * @param Form $form Scheduler's task configuration form
	 * @throws JsonException
	 */
	public function save(Form $form): void {
		try {
			$values = $form->getValues(true);
			$pData = &$values['task']['message']['data']['req']['pData'];
			$pData = explode('.', $pData);
			$this->manager->save($values, $this->id);
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
