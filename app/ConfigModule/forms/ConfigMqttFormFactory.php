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

namespace App\ConfigModule\Forms;

use App\ConfigModule\Presenters\MqttPresenter;
use App\Forms\FormFactory;
use App\Model\ConfigManager;
use App\Model\ConfigParser;
use Nette;
use Nette\Application\UI\Form;

class ConfigMqttFormFactory {

	use Nette\SmartObject;

	/**
	 * @var ConfigManager
	 */
	private $configManager;

	/**
	 * @var ConfigParser
	 */
	private $configParser;

	/**
	 * @var FormFactory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param FormFactory $factory
	 * @param ConfigManager $configManager
	 * @param ConfigParser $configParser
	 */
	public function __construct(FormFactory $factory, ConfigManager $configManager, ConfigParser $configParser) {
		$this->factory = $factory;
		$this->configManager = $configManager;
		$this->configParser = $configParser;
	}

	/**
	 * Create MQTT configuration form
	 * @param MqttPresenter $presenter
	 * @return Form MQTT configuration form
	 */
	public function create(MqttPresenter $presenter) {
		$id = $presenter->getParameter('id');
		$form = $this->factory->create();
		$fileName = 'MqttMessaging';
		$json = $this->configManager->read($fileName);
		$qos = ['QoS 0 - At most once', 'QoS 1 - At least once', 'QoS 2 - Exactly once'];
		$form->addText('Name', 'Name')->setRequired();
		$form->addCheckbox('Enabled', 'Enabled');
		$form->addText('BrokerAddr', 'BrokerAddr')->setRequired();
		$form->addText('ClientId', 'ClientId')->setRequired();
		$form->addInteger('Persistence', 'Persistence');
		$form->addSelect('Qos', 'QoS', $qos);
		$form->addText('TopicRequest', 'TopicRequest')->setRequired();
		$form->addText('TopicResponse', 'TopicResponse')->setRequired();
		$form->addText('User', 'User');
		$form->addText('Password', 'Password');
		$form->addCheckbox('EnabledSSL', 'EnabledSSL');
		$form->addInteger('KeepAliveInterval', 'KeepAliveInterval');
		$form->addInteger('ConnectTimeout', 'ConnectTimeout');
		$form->addInteger('MinReconnect', 'MinReconnect');
		$form->addInteger('MaxReconnect', 'MaxReconnect');
		$form->addText('TrustStore', 'TrustStore');
		$form->addText('KeyStore', 'KeyStore');
		$form->addText('PrivateKey', 'PrivateKey');
		$form->addPassword('PrivateKeyPassword', 'PrivateKeyPassword');
		$form->addText('EnabledCipherSuites', 'EnabledCipherSuites');
		$form->addCheckbox('EnableServerCertAuth', 'EnableServerCertAuth');
		$form->addSubmit('save', 'Save');
		$form->setDefaults($this->configParser->instancesToForm($json, $id));
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter, $fileName, $id) {
			$this->configManager->saveInstances($fileName, $values, $id);
			$presenter->redirect('Mqtt:default');
		};
		return $form;
	}

}
