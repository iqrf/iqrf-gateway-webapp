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
		'ERR' => 'VerbosityLevels.Error',
		'WAR' => 'VerbosityLevels.Warning',
		'INF' => 'VerbosityLevels.Info',
		'DBG' => 'VerbosityLevels.Debug',
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
		$defaults = $this->load();
		$form = $this->factory->create('config.tracer.form');
		$form->addText('instance', 'instance');
		$form->addText('path', 'path');
		$form->addText('filename', 'filename');
		$form->addInteger('maxSizeMB', 'maxSizeMB');
		$form->addCheckbox('timestampFiles', 'timestampFiles');
		$verbosityLevels = $form->addContainer('VerbosityLevels');
		foreach ($defaults['VerbosityLevels'] as $id => $verbosityLevel) {
			$container = $verbosityLevels->addContainer($id);
			$container->addInteger('channel', 'channel');
			$container->addSelect('level', 'level', $this->verbosityLevels)
				->setDefaultValue(Strings::upper($verbosityLevel['level']));
			unset($defaults['VerbosityLevels'][$id]['level']);
		}
		$form->addSubmit('save', 'Save');
		$form->setDefaults($defaults);
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Loads the tracer configuration
	 * @return mixed[] Tracer configuration
	 */
	private function load(): array {
		$id = intval($this->presenter->getParameter('id'));
		try {
			$defaults = $this->manager->load($id);
		} catch (JsonException $e) {
			$defaults = [];
		}
		return $defaults;
	}

}
