<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
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

namespace App\Forms;

use App\Model\ConfigManager;
use App\Presenters\ConfigPresenter;

use GettextTranslator\Gettext;

use Nette;
use Nette\Application\UI\Form;

class ConfigIqrfFormFactory {

	use Nette\SmartObject;

	/**
	 * @var ConfigManager
	 * @inject
	 */
	private $configManager;

	/**
	 * @var FormFactory
	 * @inject
	 */
	private $factory;

	/**
	 * @var Gettext
	 * @inject
	 */
	private $translator;

	public function __construct(FormFactory $factory, ConfigManager $configManager, Gettext $translator) {
		$this->factory = $factory;
		$this->configManager = $configManager;
		$this->translator = $translator;
	}

	/**
	 * Create IQRF configuration form
	 * @param ConfigPresenter $presenter
	 * @return Form IQRF configuration form
	 */
	public function create(ConfigPresenter $presenter) {
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$json = $this->configManager->read('IqrfInterface');
		$form->addText('IqrfInterface', 'IqrfInterface');
		$form->addSubmit('save', 'Save');
		$form->setDefaults($json);
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			$this->configManager->write('IqrfInterface', $values);
			$presenter->redirect('Config:default');
		};
		return $form;
	}

}
