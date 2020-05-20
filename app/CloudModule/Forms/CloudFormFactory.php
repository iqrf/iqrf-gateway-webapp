<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

use App\CloudModule\Exceptions\CannotCreateCertificateDirectoryException;
use App\CloudModule\Exceptions\InvalidConnectionStringException;
use App\CloudModule\Exceptions\InvalidPrivateKeyForCertificateException;
use App\CloudModule\Models\IManager;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use App\CoreModule\Presenters\ProtectedPresenter;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use GuzzleHttp\Exception\TransferException;
use Nette\Forms\Controls\SubmitButton;
use Nette\IOException;

/**
 * Generic form factory for the cloud services
 */
abstract class CloudFormFactory {

	/**
	 * @var FormFactory Generic form factory
	 */
	protected $factory;

	/**
	 * @var ProtectedPresenter Protected presenter
	 */
	protected $presenter;

	/**
	 * @var IManager Cloud service manager
	 */
	private $manager;

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
	 * Saves the MQTT interface
	 * @param SubmitButton $button Form's submit button
	 * @param bool $needRestart Is restart needed?
	 */
	public function save(SubmitButton $button, bool $needRestart = false): void {
		$values = $button->getForm()->getValues('array');
		assert(is_array($values));
		try {
			$this->manager->createMqttInterface($values);
			$this->presenter->flashSuccess('cloud.messages.success');
			if ($needRestart) {
				$this->serviceManager->restart();
				$this->presenter->flashInfo('service.iqrf-gateway-daemon.messages.restart');
			}
			$this->presenter->redirect(':Config:Mqtt:default');
		} catch (InvalidConnectionStringException $e) {
			$this->presenter->flashError('cloud.msAzure.messages.invalidConnectionString');
		} catch (InvalidPrivateKeyForCertificateException $e) {
			$this->presenter->flashError('cloud.amazonAws.messages.mismatchedCrtAndKey');
		} catch (NonexistentJsonSchemaException $e) {
			$this->presenter->flashError('config.messages.writeFailures.nonExistingJsonSchema');
		} catch (IOException $e) {
			$this->presenter->flashError('config.messages.writeFailures.ioError');
		} catch (TransferException $e) {
			$this->presenter->flashError('cloud.messages.downloadFailure');
		} catch (CannotCreateCertificateDirectoryException $e) {
			$this->presenter->flashError('cloud.messages.cannotCreateDir');
		} catch (UnsupportedInitSystemException $e) {
			$this->presenter->flashError('service.errors.unsupportedInit');
			$this->presenter->redirect(':Config:Mqtt:default');
		}
	}

}
