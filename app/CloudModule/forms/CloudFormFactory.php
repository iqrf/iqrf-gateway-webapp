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
declare(strict_types=1);

namespace App\CloudModule\Forms;

use App\CloudModule\Exception\InvalidConnectionStringException;
use App\CloudModule\Exception\InvalidPrivateKeyForCertificateException;
use App\CloudModule\Model\IManager;
use App\CoreModule\Exception\NonExistingJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use App\CoreModule\Presenters\ProtectedPresenter;
use App\ServiceModule\Exception\NotSupportedInitSystemException;
use App\ServiceModule\Model\ServiceManager;
use GuzzleHttp\Exception\TransferException;
use Nette;
use Nette\Forms\Controls\SubmitButton;
use Nette\IOException;

/**
 * Generic form factory for the cloud services
 */
class CloudFormFactory {

	use Nette\SmartObject;

	/**
	 * @var IManager Cloud service manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	protected $factory;

	/**
	 * @var ProtectedPresenter Protected presenter
	 */
	protected $presenter;

	/**
	 * @var ServiceManager Service manager
	 */
	private $serviceManager;

	/**
	 * Constructor
	 * @param IManager $manager Cloud service manager
	 * @param FormFactory $factory Generic form factory
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(IManager $manager, FormFactory $factory, ServiceManager $serviceManager) {
		$this->manager = $manager;
		$this->factory = $factory;
		$this->serviceManager = $serviceManager;
	}

	/**
	 * Create the MQTT interface
	 * @param SubmitButton $button Form's sumbit button
	 * @param bool $needRestart Is restart needed?
	 */
	public function save(SubmitButton $button, bool $needRestart = false): void {
		$values = $button->getForm()->getValues(true);
		try {
			$this->manager->createMqttInterface($values);
			$this->presenter->flashMessage('cloud.messages.success', 'success');
			$this->presenter->redirect(':Config:Mqtt:default');
		} catch (InvalidConnectionStringException $e) {
			$this->presenter->flashMessage('cloud.msAzure.messages.invalidConnectionString', 'danger');
		} catch (InvalidPrivateKeyForCertificateException $e) {
			$this->presenter->flashMessage('cloud.amazonAws.messages.mismatchedCrtAndKey', 'danger');
		} catch (NonExistingJsonSchemaException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.nonExistingJsonSchema', 'danger');
		} catch (IOException $e) {
			$this->presenter->flashMessage('config.messages.writeFailures.ioError', 'danger');
		} catch (TransferException $e) {
			$this->presenter->flashMessage('cloud.messages.downloadFailure', 'danger');
		}
		if ($needRestart) {
			try {
				$this->serviceManager->restart();
				$this->presenter->flashMessage('service.actions.restart.message', 'info');
			} catch (NotSupportedInitSystemException $e) {
				$this->presenter->flashMessage('service.errors.unsupportedInit', 'danger');
			}
		}
	}

}
