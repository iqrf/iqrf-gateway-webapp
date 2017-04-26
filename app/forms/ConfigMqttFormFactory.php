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

use Nette;
use Nette\Application\UI\Form;
use App\Presenters\ConfigPresenter;
use App\Model\ConfigManager;

class ConfigMqttFormFactory {

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

	public function __construct(FormFactory $factory, ConfigManager $configManager) {
		$this->factory = $factory;
		$this->configManager = $configManager;
	}

	/**
	 * Create MQTT configuration form
	 * @param ConfigPresenter $presenter
	 * @return Form MQTT configuration form
	 */
	public function create(ConfigPresenter $presenter) {
		$id = $presenter->id;
		$form = $this->factory->create();
		$fileName = 'MqttMessaging';
		$instances = $this->configManager->read($fileName)['Instances'];
		$data = $instances[$id];
		$properties = $data['Properties'];
		$form->addText('Name', 'Name')->setDefaultValue($data['Name']);
		$form->addCheckbox('Enabled', 'Enabled')->setDefaultValue($data['Enabled']);
		$form->addText('BrokerAddr', 'Broker address')->setDefaultValue($properties['BrokerAddr']);
		$form->addText('ClientId', 'Client ID')->setDefaultValue($properties['ClientId']);
		$form->addInteger('Persistence', 'Persistence')->setDefaultValue($properties['Persistence']);
		$form->addInteger('Qos', 'QoS')->setDefaultValue($properties['Qos'])->addRule(Form::RANGE, 'QoS 0-2', [0, 2]);
		$form->addText('TopicRequest', 'TopicRequest')->setDefaultValue($properties['TopicRequest']);
		$form->addText('TopicResponse', 'TopicResponse')->setDefaultValue($properties['TopicResponse']);
		$form->addText('User', 'User')->setDefaultValue($properties['User']);
		$form->addPassword('Password', 'Password')->setDefaultValue($properties['Password']);
		$form->addCheckbox('EnabledSSL', 'Enabled TLS')->setDefaultValue($properties['EnabledSSL']);
		$form->addInteger('KeepAliveInterval', 'Keep alive interval')->setDefaultValue($properties['KeepAliveInterval']);
		$form->addInteger('ConnectTimeout', 'Connection timeout')->setDefaultValue($properties['ConnectTimeout']);
		$form->addInteger('MinReconnect', 'Min reconnect')->setDefaultValue($properties['MinReconnect']);
		$form->addInteger('MaxReconnect', 'Max reconnect')->setDefaultValue($properties['MaxReconnect']);
		$form->addText('TrustStore', 'TrustStore')->setDefaultValue($properties['TrustStore']);
		$form->addText('KeyStore', 'KeyStore')->setDefaultValue($properties['KeyStore']);
		$form->addText('PrivateKey', 'PrivateKey')->setDefaultValue($properties['PrivateKey']);
		$form->addPassword('PrivateKeyPassword', 'PrivateKeyPassword')->setDefaultValue($properties['PrivateKeyPassword']);
		$form->addText('EnabledCipherSuites', 'EnabledCipherSuites')->setDefaultValue($properties['EnabledCipherSuites']);
		$form->addCheckbox('EnableServerCertAuth', 'EnableServerCertAuth')->setDefaultValue($properties['EnableServerCertAuth']);
		$form->addSubmit('save', 'Save');
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter, $fileName, $id) {
			$this->configManager->saveInstances($fileName, $values, $id);
			$presenter->redirect('Config:default');
		};

		return $form;
	}

}
