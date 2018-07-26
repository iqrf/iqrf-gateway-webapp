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

namespace App\CloudModule\Forms;

use App\CloudModule\Model\AwsManager;
use App\CloudModule\Presenters\AwsPresenter;
use App\CloudModule\Model\InvalidPrivateKeyForCertificate;
use App\Forms\FormFactory;
use App\ServiceModule\Model\NotSupportedInitSystemException;
use App\ServiceModule\Model\ServiceManager;
use Nette;
use Nette\Forms\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\IOException;
use Nette\Utils\ArrayHash;

/**
 * Form for creating MQTT instance and Base service for Amazon AWS IoT
 */
class CloudAwsMqttFormFactory {

	use Nette\SmartObject;

	/**
	 * @var AwsManager Amazon AWS IoT manager
	 */
	private $cloudManager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var ServiceManager Service manager
	 */
	private $serviceManager;

	/**
	 * Constructor
	 * @param AwsManager $aws Amazon AWS IoT manager
	 * @param FormFactory $factory Generic form factory
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(AwsManager $aws, FormFactory $factory, ServiceManager $serviceManager) {
		$this->cloudManager = $aws;
		$this->factory = $factory;
		$this->serviceManager = $serviceManager;
	}

	/**
	 * Create MQTT configuration form
	 * @param AwsPresenter $presenter Amazon AWS IoT presenter
	 * @return Form MQTT configuration form
	 */
	public function create(AwsPresenter $presenter): Form {
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('cloud.amazonAws.form'));
		$form->addText('endpoint', 'endpoint')->setRequired();
		$form->addUpload('cert', 'certificate')->setRequired();
		$form->addUpload('key', 'pkey')->setRequired();
		$form->addSubmit('save', 'save')
				->onClick[] = function (SubmitButton $button) use ($presenter) {
			$values = $button->getForm()->getValues();
			$this->save($values, $presenter);
		};
		$form->addSubmit('save_restart', 'save_restart')
				->onClick[] = function (SubmitButton $button) use ($presenter) {
			$values = $button->getForm()->getValues();
			$this->save($values, $presenter, true);
		};
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

	/**
	 * Create the base service and MQTT interface
	 * @param ArrayHash $values Values from the form
	 * @param AwsPresenter $presenter Amazon AWS IoT presenter
	 * @param bool $needRestart Is restart needed?
	 * @throws InvalidPrivateKeyForCertificate Invalid the private key for the certificate exception
	 * @throws IOException Nette IO exception
	 * @throws NotSupportedInitSystemException Not supported init system exception
	 */
	public function save(ArrayHash $values, AwsPresenter $presenter, bool $needRestart = false) {
		try {
			$this->cloudManager->createMqttInterface($values);
		} catch (\Exception $e) {
			if ($e instanceof InvalidPrivateKeyForCertificate) {
				$presenter->flashMessage('The private key doesn\'t corespond to the certificate.', 'danger');
			} else if ($e instanceof IOException) {
				$presenter->flashMessage('config.messages.writeFailure', 'danger');
			} else {
				throw $e;
			}
		}
		if ($needRestart) {
			try {
				$this->serviceManager->restart();
				$presenter->flashMessage('service.actions.restart.message', 'info');
			} catch (NotSupportedInitSystemException $e) {
				$presenter->flashMessage('service.errors.unsupportedInit', 'danger');
			}
		}
		$presenter->redirect(':Config:Mqtt:default');
	}

}
