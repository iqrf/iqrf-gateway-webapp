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

namespace App\IqrfNetModule\Presenters;

use App\CoreModule\Presenters\ProtectedPresenter;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Forms\SendRawFormFactory;
use App\IqrfNetModule\Models\DpaRawManager;
use Iqrf\IdeMacros\MacroFileParser;
use Nette\Forms\Form;
use Nette\Utils\JsonException;

/**
 * Send DPA packet presenter
 */
class SendRawPresenter extends ProtectedPresenter {

	/**
	 * @var SendRawFormFactory Send DPA packet form
	 * @inject
	 */
	public $sendRawFactory;

	/**
	 * @var DpaRawManager DPA request and response manager
	 */
	private $dpaManager;

	/**
	 * @var MacroFileParser IQRF IDE Macros parser
	 */
	private $macroParser;

	/**
	 * Constructor
	 * @param DpaRawManager $manager DPA request and response manager
	 */
	public function __construct(string $fileName, DpaRawManager $manager) {
		$this->dpaManager = $manager;
		$this->macroParser = new MacroFileParser($fileName);
		parent::__construct();
	}

	/**
	 * Renders a send DPA packet page
	 */
	public function renderDefault(): void {
		$macros = $this->macroParser->read();
		$this->template->macros = $this->macroParser->toArray($macros);
	}

	/**
	 * AJAX handler for showing DPA request and response
	 * @param mixed[] $data DPA request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function handleShowResponse(array $data): void {
		$this->template->json = $data;
		$this->template->parsedResponse = $this->dpaManager->parseResponse($data);
		$this->redrawControl('responseChange');
	}

	/**
	 * Creates the send DPA packet form
	 * @return Form Send DPA packet form
	 */
	protected function createComponentSendRawForm(): Form {
		return $this->sendRawFactory->create($this);
	}

}
