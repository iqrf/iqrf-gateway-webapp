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

namespace App\IqrfNetModule\Forms;

use App\CoreModule\Forms\FormFactory;
use App\GatewayModule\Exceptions\CorruptedFileException;
use App\IqrfNetModule\Entities\IqrfOs;
use App\IqrfNetModule\Enums\UploadFormats;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Models\IqrfOsManager;
use App\IqrfNetModule\Models\OsManager;
use App\IqrfNetModule\Models\UploadManager;
use App\IqrfNetModule\Presenters\TrUploadPresenter;
use App\ServiceModule\Models\ServiceManager;
use GuzzleHttp\Exception\ClientException;
use Nette\Application\UI\Form;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * IQRF OS Upload form factory
 */
class OsDpaUploadFormFactory {

	use SmartObject;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $formFactory;

	/**
	 * @var IqrfOsManager IQRF OS manager
	 */
	private $iqrfOsManager;

	/**
	 * @var IqrfOs Current IQRF OS entity
	 */
	private $osEntity;

	/**
	 * @var OsManager DPA OS peripheral manager
	 */
	private $osManager;

	/**
	 * @var TrUploadPresenter IQRF TR native upload presenter
	 */
	private $presenter;

	/**
	 * @var ServiceManager IQRF Gateway Daemon service manager
	 */
	private $serviceManager;

	/**
	 * @var UploadManager Native upload service manager
	 */
	private $uploadManager;

	/**
	 * Constructor
	 * @param FormFactory $formFactory Generic form factory
	 * @param OsManager $osManager DPA OS peripheral manager
	 * @param IqrfOsManager $iqrfOsManager IQRF OS manager
	 * @param ServiceManager $serviceManager IQRF Gateway Daemon service manager
	 * @param UploadManager $uploadManager Native upload service manager
	 */
	public function __construct(FormFactory $formFactory, OsManager $osManager, IqrfOsManager $iqrfOsManager, ServiceManager $serviceManager, UploadManager $uploadManager) {
		$this->formFactory = $formFactory;
		$this->osManager = $osManager;
		$this->iqrfOsManager = $iqrfOsManager;
		$this->serviceManager = $serviceManager;
		$this->uploadManager = $uploadManager;
	}

	/**
	 * Creates IQRF OS and DPA upgrade form
	 * @param TrUploadPresenter $presenter IQRF TR upload presenter
	 * @return Form IQRF OS and DPA upgrade form
	 */
	public function create(TrUploadPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->formFactory->create('iqrfnet.osUpload');
		$form->addSelect('version', 'version')
			->setItems($this->list())
			->setTranslator($this->formFactory->getTranslator())
			->setRequired('messages.version');
		$form->addSubmit('upload', 'upload')
			->setHtmlAttribute('class', 'ajax');
		$form->onSuccess[] = [$this, 'upload'];
		return $form;
	}

	/**
	 * Returns available IQRF OS changes
	 * @return string[] Available IQRF OS changes
	 */
	private function list(): array {
		try {
			$osRead = $this->osManager->read(0);
			$this->osEntity = IqrfOs::fromOsRead($osRead);
			return $this->iqrfOsManager->list($this->osEntity);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			return [];
		}
	}

	/**
	 * Uploads a file into IQRF TR
	 * @param Form $form IQRF TR native upload form
	 */
	public function upload(Form $form): void {
		$array = explode(',', $form->getValues()->version);
		$osBuild = $array[0];
		$dpa = $array[1];
		$rfMode = $array[2] ?? null;
		try {
			$files = $this->iqrfOsManager->getFiles($this->osEntity, $osBuild, $dpa, $rfMode);
			foreach ($files as $file) {
				$this->uploadManager->uploadFile($file, UploadFormats::IQRF());
			}
			$this->serviceManager->restart();
			$this->presenter->flashSuccess('iqrfnet.trUpload.messages.success');
			$this->presenter->flashInfo('service.actions.restart.message');
		} catch (CorruptedFileException $e) {
			$this->presenter->flashError('iqrfnet.trUpload.messages.corruptedFile');
		} catch (DpaErrorException | EmptyResponseException | JsonException | UserErrorException $e) {
			$this->presenter->flashError('iqrfnet.trUpload.messages.failure');
		} catch (IOException $e) {
			$this->presenter->flashError('iqrfnet.trUpload.messages.moveFailure');
		} catch (ClientException $e) {
			$this->presenter->flashError('iqrfnet.trUpload.messages.downloadFailure');
		}
	}

}
