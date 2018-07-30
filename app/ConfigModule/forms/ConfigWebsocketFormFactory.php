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

use App\ConfigModule\Model\WebsocketManager;
use App\ConfigModule\Presenters\WebsocketPresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Forms\Form;
use Nette\IOException;

class ConfigWebsocketFormFactory {

	use Nette\SmartObject;

	/**
	 * @var WebsocketManager Websocket configuration manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param WebsocketManager $manager Websocket configuration manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(WebsocketManager $manager, FormFactory $factory) {
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create websocket configuration form
	 * @param WebsocketPresenter $presenter Websocket presenter
	 * @return Form Websocket configuration form
	 */
	public function create(WebsocketPresenter $presenter): Form {
		$id = intval($presenter->getParameter('id'));
		$form = $this->factory->create();
		$translator = $form->getTranslator();
		$form->setTranslator($translator->domain('config.websocket.form'));
		$form->addInteger('port', 'port')->setRequired('messages.port');
		$form->addCheckbox('acceptAsyncMsg', 'acceptAsyncMsg');
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		if (array_key_exists($id, $this->manager->getInstanceFiles('iqrf::WebsocketMessaging'))) {
			$form->setDefaults($this->manager->load($id));
		}
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			try {
				$this->manager->save($values);
				$presenter->flashMessage('config.messages.success', 'success');
			} catch (IOException $e) {
				$presenter->flashMessage('config.messages.writeFailure', 'danger');
			} finally {
				$presenter->redirect('Websocket:default');
			}
		};
		return $form;
	}

}
