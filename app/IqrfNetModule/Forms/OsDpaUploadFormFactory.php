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
use App\IqrfNetModule\Enums\UploadFormats;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Models\IqrfOsManager;
use App\IqrfNetModule\Models\NativeUploadManager;
use App\IqrfNetModule\Presenters\TrUploadPresenter;
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
	 * @var TrUploadPresenter IQRF TR native upload presenter
	 */
	private $presenter;

	/**
	 * @var IqrfOsManager IQRF OS manager
	 */
	private $osManager;

	/**
	 * @var NativeUploadManager Native upload service manager
	 */
	private $uploadManager;

	/**
	 * Constructor
	 * @param FormFactory $formFactory Generic form factory
	 * @param IqrfOsManager $osManager IQRF OS manager
	 * @param NativeUploadManager $uploadManager Native upload service manager
	 */
	public function __construct(FormFactory $formFactory, IqrfOsManager $osManager, NativeUploadManager $uploadManager) {
		$this->formFactory = $formFactory;
		$this->osManager = $osManager;
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
			->setItems($this->osManager->list())
			->setTranslator($this->formFactory->getTranslator());
		$form->addSubmit('upload', 'upload');
		$form->onSuccess[] = [$this, 'upload'];
		return $form;
	}

	/**
	 * Uploads a file into IQRF TR
	 * @param Form $form IQRF TR native upload form
	 */
	public function upload(Form $form): void {
		$array = explode(',', $form->getValues()->version);
		$osBuild = $array[0];
		$dpaVersion = $array[1];
		$rfMode = $array[2] ?? null;
		try {
			$files = $this->osManager->getFiles($osBuild, $dpaVersion, $rfMode);
			foreach ($files as $file) {
				$this->uploadManager->uploadFile($file, UploadFormats::IQRF());
			}
			$this->presenter->flashSuccess('iqrfnet.trUpload.messages.success');
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
