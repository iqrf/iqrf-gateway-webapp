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

use App\ConfigModule\Forms\GenericConfigFormFactory;
use App\ConfigModule\Presenters\UdpPresenter;
use Nette;
use Nette\Forms\Form;

/**
 * UDP interface configuration form factory
 */
class UdpFormFactory extends GenericConfigFormFactory {

	use Nette\SmartObject;

	/**
	 * Create UDP interface configuration form
	 * @param UdpPresenter $presenter UDP interface presenter
	 * @return Form UDP interface configuration form
	 */
	public function create(UdpPresenter $presenter): Form {
		$this->manager->setComponent('iqrf::UdpMessaging');
		$this->redirect = 'Udp:default';
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.udp.form'));
		$form->addText('instance', 'instance')->setRequired('messages.instance');
		$form->addInteger('RemotePort', 'RemotePort')->setRequired('messages.RemotePort');
		$form->addInteger('LocalPort', 'LocalPort')->setRequired('messages.LocalPort');
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		$id = $presenter->getParameter('id');
		if (isset($id)) {
			$form->setDefaults($this->manager->load(intval($id)));
		}
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Save UDP interface configuration
	 * @param Form $form IDP interface configuration form
	 */
	public function save(Form $form): void {
		$instances = $this->manager->getInstanceFiles();
		$id = intval($this->presenter->getParameter('id'));
		if (array_key_exists($id, $instances) && count($instances) >= 1) {
			$this->presenter->flashMessage('config.messages.writeFailures.multipleInstancesFailure', 'danger');
			$this->presenter->redirect('Udp:default');
		}
		parent::save($form);
	}

}
