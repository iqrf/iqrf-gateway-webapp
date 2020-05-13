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

use App\ConfigModule\Presenters\TracerPresenter;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

/**
 * Tracer configuration form factory
 */
class TraceFileFormFactory extends GenericConfigFormFactory {

	use SmartObject;

	/**
	 * @var string[] Verbosity levels
	 */
	private $verbosityLevels = [
		'ERR' => 'levels.Error',
		'WAR' => 'levels.Warning',
		'INF' => 'levels.Info',
		'DBG' => 'levels.Debug',
	];

	/**
	 * Creates the Tracer configuration form
	 * @param TracerPresenter $presenter Tracer configuration presenter
	 * @return Form Tracer configuration form
	 */
	public function create(TracerPresenter $presenter): Form {
		$this->manager->setComponent('shape::TraceFileService');
		$this->redirect = 'Tracer:default';
		$this->presenter = $presenter;
		$form = $this->factory->create('config.tracer.form');
		$form->addGroup();
		$form->addText('instance', 'instance');
		$form->addText('path', 'path');
		$form->addText('filename', 'filename');
		$form->addInteger('maxSizeMB', 'maxSizeMB');
		$form->addCheckbox('timestampFiles', 'timestampFiles');
		$form->addGroup('verbosityLevels.title');
		$verbosityLevels = $form->addMultiplier('VerbosityLevels', [$this, 'createVerbosityLevelsMultiplier'], 1);
		$verbosityLevels->addCreateButton('verbosityLevels.add')
			->addClass('btn btn-success');
		$verbosityLevels->addRemoveButton('verbosityLevels.remove')
			->addClass('btn btn-danger');
		$form->addGroup();
		$form->addSubmit('save', 'Save');
		$form->setDefaults($this->load());
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Creates trace file verbosity levels
	 * @param Container $container Container for verbosity levels
	 */
	public function createVerbosityLevelsMultiplier(Container $container): void {
		$container->addInteger('channel', 'channel')
			->setRequired('messages.verbosityLevels.channel');
		$container->addSelect('level', 'level', $this->verbosityLevels)
			->setRequired('messages.verbosityLevels.level')
			->setPrompt('messages.verbosityLevels.level');
	}

	/**
	 * Loads the tracer configuration
	 * @return mixed[] Tracer configuration
	 */
	private function load(): array {
		$id = (int) $this->presenter->getParameter('id');
		try {
			$defaults = $this->manager->load($id);
			foreach ($defaults['VerbosityLevels'] as &$verbosityLevel) {
				$level = Strings::upper($verbosityLevel['level']);
				if (array_key_exists($level, $this->verbosityLevels)) {
					$verbosityLevel['level'] = $level;
				} else {
					unset($verbosityLevel['level']);
				}
			}
		} catch (JsonException $e) {
			$defaults = [];
		}
		return $defaults;
	}

}
