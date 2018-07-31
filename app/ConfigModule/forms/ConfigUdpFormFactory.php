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
use App\ConfigModule\Presenters\UdpPresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Forms\Form;
use Nette\IOException;

class ConfigUdpFormFactory {

	use Nette\SmartObject;

	/**
	 * @var GenericManager Generic config manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 *
	 * @var array Files with UDP interface instances
	 */
	private $instances;

	/**
	 * @var int UDP interface ID
	 */
	private $id;

	/**
	 * @var UdpPresenter UDP interface presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param GenericManager $manager Generic configuration manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(GenericManager $manager, FormFactory $factory) {
		$this->manager = $manager;
		$this->factory = $factory;
		$this->manager->setComponent('iqrf::UdpMessaging');
		$this->instances = $this->manager->getInstanceFiles();
	}

	/**
	 * Create UDP interface configuration form
	 * @param UdpPresenter $presenter UDP interface presenter
	 * @return Form UDP interface configuration form
	 */
	public function create(UdpPresenter $presenter): Form {
		$this->presenter = $presenter;
		$this->id = intval($presenter->getParameter('id'));
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.udp.form'));
		$form->addText('instance', 'instance')->setRequired('messages.instance');
		$form->addInteger('RemotePort', 'RemotePort')->setRequired('messages.RemotePort');
		$form->addInteger('LocalPort', 'LocalPort')->setRequired('messages.LocalPort');
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		if ($this->isExists()) {
			$this->manager->setFileName($this->instances[$this->id]);
			$form->setDefaults($this->manager->load());
		}
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Check if instance exists
	 * @return bool Is instance exists?
	 */
	public function isExists(): bool {
		return array_key_exists($this->id, $this->instances);
	}

	/**
	 * Save UDP interface configuration
	 * @param Form $form IDP interface configuration form
	 */
	public function save(Form $form) {
		$values = $form->getValues();
		if (!$this->isExists() && count($this->instances) >= 1) {
			$this->presenter->flashMessage('config.messages.multipleInstancesFailure', 'danger');
			$this->presenter->redirect('Udp:default');
		}
		if (!$this->isExists()) {
			$this->manager->setFileName('iqrf__' . $values['instance']);
		}
		try {
			$this->manager->save($values);
			$this->presenter->flashMessage('config.messages.success', 'success');
		} catch (IOException $e) {
			$this->presenter->flashMessage('config.messages.writeFailure', 'danger');
		} finally {
			$this->presenter->redirect('Udp:default');
		}
	}

}
