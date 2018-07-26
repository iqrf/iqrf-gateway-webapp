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
use App\ConfigModule\Presenters\MqttPresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Forms\Form;
use Nette\IOException;

class ConfigMqttFormFactory {

	use Nette\SmartObject;

	/**
	 * @var GenericManager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param GenericManager $manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(GenericManager $manager, FormFactory $factory) {
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create MQTT configuration form
	 * @param MqttPresenter $presenter
	 * @return Form MQTT configuration form
	 */
	public function create(MqttPresenter $presenter): Form {
		$id = intval($presenter->getParameter('id'));
		$this->manager->setComponent('iqrf::MqttMessaging');
		$instances = $this->manager->getInstanceFiles();
		$instanceExist = array_key_exists($id, $instances);
		$qos = ['QoSes.QoS0', 'QoSes.QoS1', 'QoSes.QoS2'];
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.mqtt.form'));
		$form->addText('instance', 'instance')->setRequired('messages.instance');
		$form->addText('BrokerAddr', 'BrokerAddr')
				->setRequired('messages.BrokerAddr');
		$form->addText('ClientId', 'ClientId')
				->setRequired('messages.ClientId');
		$form->addInteger('Persistence', 'Persistence');
		$form->addSelect('Qos', 'QoS', $qos);
		$form->addText('TopicRequest', 'TopicRequest')
				->setRequired('messages.TopicRequest');
		$form->addText('TopicResponse', 'TopicResponse')
				->setRequired('messages.TopicResponse');
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
		$form->addText('PrivateKeyPassword', 'PrivateKeyPassword');
		$form->addText('EnabledCipherSuites', 'EnabledCipherSuites');
		$form->addCheckbox('EnableServerCertAuth', 'EnableServerCertAuth');
		$form->addCheckbox('acceptAsyncMsg', 'acceptAsyncMsg');
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		if ($instanceExist) {
			$this->manager->setFileName($instances[$id]);
			$form->setDefaults($this->manager->load());
		}
		$form->onSuccess[] = function (Form $form, $values) use ($presenter, $instanceExist) {
			if (!$instanceExist) {
				$this->manager->setFileName('iqrf__' . $values['instance']);
			}
			try {
				$this->manager->save($values);
				$presenter->flashMessage('config.messages.success', 'success');
			} catch (IOException $e) {
				$presenter->flashMessage('config.messages.writeFailure', 'danger');
			} finally {
				$presenter->redirect('Mqtt:default');
			}
		};
		return $form;
	}

}
