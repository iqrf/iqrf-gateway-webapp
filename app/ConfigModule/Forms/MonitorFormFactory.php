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

use App\ConfigModule\Models\MonitorManager;
use App\ConfigModule\Presenters\MonitorPresenter;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use Nette\Application\UI\Form;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * Daemon's monitor service configuration form factory
 */
class MonitorFormFactory {

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var MonitorPresenter Daemon's monitor service configuration presenter
	 */
	private $presenter;

	/**
	 * @var MonitorManager Daemon's monitor service manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param MonitorManager $manager Daemon's monitor service manager
	 */
	public function __construct(FormFactory $factory, MonitorManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates the daemon's monitor service configuration form
	 * @param MonitorPresenter $presenter Daemon's monitor service configuration presenter
	 * @return Form Daemon's monitor service configuration form
	 */
	public function create(MonitorPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('config.monitor.form');
		$form->addInteger('reportPeriod', 'reportPeriod')
			->setRequired('messages.reportPeriod');
		$form->addInteger('port', 'WebsocketPort')
			->setRequired('messages.WebsocketPort');
		$form->addCheckbox('acceptOnlyLocalhost', 'acceptOnlyLocalhost');
		$form->addSubmit('save', 'save');
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Saves the Daemon's monitor service configuration
	 * @param Form $form Daemon's monitor service configuration form
	 * @throws JsonException
	 */
	public function save(Form $form): void {
		try {
			$values = $form->getValues('array');
			assert(is_array($values));
			$this->manager->save($values);
			$this->presenter->flashSuccess('config.messages.success');
		} catch (NonexistentJsonSchemaException $e) {
			$this->presenter->flashError('config.messages.writeFailures.nonExistingJsonSchema');
		} catch (IOException $e) {
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} finally {
			$this->presenter->redirect('Monitor:default');
		}
	}

}
