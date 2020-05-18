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

use App\ConfigModule\Presenters\MqPresenter;
use Nette\Application\UI\Form;

/**
 * MQ interface configuration form factory
 */
class MqFormFactory extends GenericConfigFormFactory {

	/**
	 * Creates the MQ interface configuration form
	 * @param MqPresenter $presenter MQ interface configuration presenter
	 * @return Form MQ interface configuration form
	 */
	public function create(MqPresenter $presenter): Form {
		$this->manager->setComponent('iqrf::MqMessaging');
		$this->redirect = 'Mq:default';
		$this->presenter = $presenter;
		$form = $this->factory->create('config.mq.form');
		$form->addText('instance', 'instance')
			->setRequired('messages.instance');
		$form->addText('LocalMqName', 'LocalMqName')
			->setRequired('messages.LocalMqName');
		$form->addText('RemoteMqName', 'RemoteMqName')
			->setRequired('messages.RemoteMqName');
		$form->addCheckbox('acceptAsyncMsg', 'acceptAsyncMsg');
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

}
