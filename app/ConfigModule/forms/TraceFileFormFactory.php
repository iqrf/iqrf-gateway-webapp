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

use App\ConfigModule\Presenters\TracerPresenter;
use Nette\Forms\Form;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

class TraceFileFormFactory extends GenericConfigFormFactory {

	use SmartObject;

	/**
	 * Create Tracer configuration form
	 * @param TracerPresenter $presenter
	 * @return Form Tracer configuration form
	 */
	public function create(TracerPresenter $presenter): Form {
		$this->manager->setComponent('shape::TraceFileService');
		$this->redirect = 'Tracer:default';
		$this->presenter = $presenter;
		$id = intval($presenter->getParameter('id'));
		try {
			$defaults = $this->manager->load($id);
		} catch (JsonException $e) {
			$defaults = [];
		}
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.tracer.form'));
		$items = ['err' => 'VerbosityLevels.Error', 'war' => 'VerbosityLevels.Warning',
			'inf' => 'VerbosityLevels.Info', 'dbg' => 'VerbosityLevels.Debug'];
		$form->addText('instance', 'instance');
		$form->addText('path', 'path');
		$form->addText('filename', 'filename');
		$form->addInteger('maxSizeMB', 'maxSizeMB');
		$form->addCheckbox('timestampFiles', 'timestampFiles');
		$verbosityLevels = $form->addContainer('VerbosityLevels');
		foreach ($defaults['VerbosityLevels'] as $id => $verbosityLevel) {
			$container = $verbosityLevels->addContainer($id);
			$container->addInteger('channel', 'channel');
			$container->addSelect('level', 'level', $items)
					->setDefaultValue(Strings::lower($verbosityLevel['level']));
			unset($defaults['VerbosityLevels'][$id]['level']);
		}
		$form->addSubmit('save', 'Save');
		$form->setDefaults($defaults);
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			$this->manager->save($values);
			$presenter->redirect('Homepage:default');
		};
		return $form;
	}

}
