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

use App\ConfigModule\Model\GenericManager;
use App\ConfigModule\Presenters\IqrfPresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Forms\Form;

class ConfigIqrfIde4FormFactory {

	use Nette\SmartObject;

	/**
	 * @var GenericManager Generic configuration manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param GenericManager $manager Generic configuration manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(GenericManager $manager, FormFactory $factory) {
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create IQRF IDE4 counterpart configuration form
	 * @param IqrfPresenter $presenter
	 * @return Form IQRF IDE4 counterpart configuration form
	 */
	public function create(IqrfPresenter $presenter): Form {
		$form = $this->factory->create();
		$translator = $form->getTranslator();
		$form->setTranslator($translator->domain('config.iqrfIde4.form'));
		$this->manager->setComponent('iqrf::Ide4Counterpart');
		$this->manager->setFileName($this->manager->getInstanceFiles()[0]);
		$defaults = $this->manager->load();
		$form->addText('instance', 'instance')->setRequired('messages.instance');
		$requiredInterfaces = $form->addContainer('RequiredInterfaces');
		foreach ($defaults['RequiredInterfaces'] as $id => $requiredInterface) {
			$container = $requiredInterfaces->addContainer($id);
			$container->addSelect('name', 'config.iqrfIde4.form.requiredInterface.name')
					->setItems(['iqrf::IMessagingService',], false)
					->setTranslator($translator)
					->setRequired('messages.requiredInterface.name');
			$target = $container->addContainer('target');
			$target->addSelect('instance', 'config.iqrfIde4.form.requiredInterface.instance')
					->setItems($this->manager->getMessagings(), false)
					->setTranslator($translator)
					->setRequired('messages.requiredInterface.instance')
					->setDefaultValue($requiredInterface['target']['instance']);
		}
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		$form->setDefaults($defaults);
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			$this->manager->save($values);
			$presenter->redirect('Iqrf:default');
		};
		return $form;
	}

}
