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

namespace App\IqrfAppModule\Presenters;

use App\CoreModule\Presenters\ProtectedPresenter;
use App\IqrfAppModule\Exception\DpaErrorException;
use App\IqrfAppModule\Exception\EmptyResponseException;
use App\IqrfAppModule\Exception\UserErrorException;
use App\IqrfAppModule\Forms\SendRawFormFactory;
use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Model\IqrfMacroManager;
use Nette\Forms\Form;
use Nette\Utils\JsonException;

/**
 * Send raw DPA packet presenter
 */
class SendRawPresenter extends ProtectedPresenter {

	/**
	 * @var SendRawFormFactory Send raw DPA packet form
	 * @inject
	 */
	public $sendRawFactory;

	/**
	 * @var IqrfAppManager iqrfapp Manager
	 */
	private $iqrfAppManager;

	/**
	 * @var IqrfMacroManager IQRF IDE Macros manager
	 */
	private $iqrfMacroManager;

	/**
	 * Constructor
	 * @param IqrfAppManager $manager iqrfapp Manager
	 * @param IqrfMacroManager $macroManager IQRF IDE Macros manager
	 */
	public function __construct(IqrfAppManager $manager, IqrfMacroManager $macroManager) {
		$this->iqrfAppManager = $manager;
		$this->iqrfMacroManager = $macroManager;
		parent::__construct();
	}

	/**
	 * Render send raw DPA packet page
	 */
	public function renderDefault(): void {
		$this->template->macros = $this->iqrfMacroManager->read();
	}

	/**
	 * AJAX handler for showing DPA request and response
	 * @param array $data DPA request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function handleShowResponse(array $data): void {
		$this->template->json = $data;
		$this->template->parsedResponse = $this->iqrfAppManager->parseResponse($data);
		$this->redrawControl('responseChange');
	}

	/**
	 * Create send raw DPA packet form
	 * @return Form Send raw DPA packet form
	 */
	protected function createComponentIqrfAppSendRawForm(): Form {
		return $this->sendRawFactory->create($this);
	}

}
