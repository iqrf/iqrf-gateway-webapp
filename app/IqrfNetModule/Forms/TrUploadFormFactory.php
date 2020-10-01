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
use App\IqrfNetModule\Models\UploadManager;
use App\IqrfNetModule\Presenters\TrUploadPresenter;
use Nette\Application\UI\Form;
use Nette\IOException;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

/**
 * IQRF TR native upload form factory
 */
class TrUploadFormFactory {

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var TrUploadPresenter IQRF TR native upload presenter
	 */
	private $presenter;

	/**
	 * @var UploadManager IQRF TR native upload manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param UploadManager $manager IQRF TR native upload manager
	 */
	public function __construct(FormFactory $factory, UploadManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates IQRF TR native upload form
	 * @param TrUploadPresenter $presenter IQRF TR native upload presenter
	 * @return Form IQRF TR native upload form
	 */
	public function create(TrUploadPresenter $presenter): Form {
		$this->presenter = $presenter;
		$files = '.hex';
		if ($this->presenter->getUser()->isInRole('power')) {
			$files .= ',.iqrf,.trcnfg';
		}
		$form = $this->factory->create('iqrfnet.trUpload');
		$form->addUpload('file', 'file')
			->setHtmlAttribute('accept', $files)
			->setRequired('messages.file');
		if ($this->presenter->getUser()->isInRole('power')) {
			$form->addSelect('fileFormat', 'fileFormat', $this->getFileFormats())
				->setPrompt('messages.fileFormat');
		}
		$form->addSubmit('upload', 'upload')
			->setHtmlAttribute('class', 'ajax');
		$form->onSuccess[] = [$this, 'upload'];
		return $form;
	}

	/**
	 * Returns File formats to upload
	 * @return array<string> File formats to upload
	 */
	private function getFileFormats(): array {
		$formats = UploadFormats::getAvailableValues();
		$array = [];
		foreach ($formats as $format) {
			$formatStr = (string) $format->toScalar();
			$array[$formatStr] = 'fileFormats.' . $formatStr;
		}
		return $array;
	}

	/**
	 * Uploads a file into IQRF TR
	 * @param Form $form IQRF TR native upload form
	 */
	public function upload(Form $form): void {
		$values = $form->getValues();
		if ($this->presenter->getUser()->isInRole('power')) {
			$fileFormat = $values->fileFormat;
			if ($fileFormat !== null) {
				$fileFormat = UploadFormats::fromScalar($fileFormat);
			}
		} else {
			$fileFormat = UploadFormats::HEX();
			$fileName = Strings::lower($values->file->getName());
			if (!Strings::endsWith($fileName, '.hex')) {
				$this->presenter->flashError('iqrfnet.trUpload.messages.invalidDpaHandler');
				$form['file']->addError('iqrfnet.trUpload.messages.invalidDpaHandler');
				return;
			}
		}
		try {
			$this->manager->upload($values->file, $fileFormat);
			$this->presenter->flashSuccess('iqrfnet.trUpload.messages.success');
		} catch (CorruptedFileException $e) {
			$this->presenter->flashError('iqrfnet.trUpload.messages.corruptedFile');
		} catch (DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->presenter->flashError('iqrfnet.trUpload.messages.failure');
		} catch (IOException $e) {
			$this->presenter->flashError('iqrfnet.trUpload.messages.moveFailure');
		}
	}

}
