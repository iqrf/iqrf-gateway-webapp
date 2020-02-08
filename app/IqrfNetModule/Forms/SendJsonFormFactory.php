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

use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use App\CoreModule\Forms\FormFactory;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Models\ApiSchemaManager;
use App\IqrfNetModule\Models\WebSocketClient;
use App\IqrfNetModule\Presenters\SendJsonPresenter;
use App\IqrfNetModule\Requests\DpaRequest;
use App\IqrfNetModule\Responses\ApiResponse;
use Contributte\Translation\Wrappers\NotTranslate;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextArea;
use Nette\SmartObject;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Send IQRF JSON request form factory
 */
class SendJsonFormFactory {

	use SmartObject;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var ApiSchemaManager API JSON schema manager
	 */
	private $jsonSchemaManager;

	/**
	 * @var SendJsonPresenter Send IQRF JSON request presenter
	 */
	private $presenter;

	/**
	 * @var DpaRequest IQRF JSON API request
	 */
	private $request;

	/**
	 * @var WebSocketClient WebSocket client
	 */
	private $wsClient;

	/**
	 * Translation prefix
	 */
	private const PREFIX = 'iqrfnet.send-json.';

	/**
	 * Constructor
	 * @param ApiSchemaManager $schemaManager API JSON schema manager
	 * @param FormFactory $factory Generic form factory
	 * @param DpaRequest $request IQRF JSON API request
	 * @param WebSocketClient $wsClient WebSocket client
	 */
	public function __construct(ApiSchemaManager $schemaManager, FormFactory $factory, DpaRequest $request, WebSocketClient $wsClient) {
		$this->factory = $factory;
		$this->jsonSchemaManager = $schemaManager;
		$this->request = $request;
		$this->wsClient = $wsClient;
	}

	/**
	 * Creates Send IQRF JSON request form
	 * @param SendJsonPresenter $presenter Send IQRF JSON request presenter
	 * @return Form Send IQRF JSON request form
	 */
	public function create(SendJsonPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->addTextArea('json', self::PREFIX . 'json')
			->setRequired(self::PREFIX . 'messages.json');
		$form->addSubmit('send', self::PREFIX . 'send')
			->setHtmlAttribute('class', self::PREFIX . 'ajax');
		$form->addProtection('core.errors.form-timeout');
		$form->onValidate[] = [$this, 'validate'];
		$form->onSuccess[] = [$this, 'send'];
		return $form;
	}

	/**
	 * Validates the IQRF JSON API request
	 * @param Form $form Send IQRF JSON request form
	 */
	public function validate(Form $form): void {
		/**
		 * @var TextArea $request JSON request textarea
		 */
		$request = $form['json'];
		try {
			$json = Json::decode($form->getValues()->json);
			if (isset($json->mType)) {
				$this->jsonSchemaManager->setSchemaForRequest($json->mType);
				$this->jsonSchemaManager->validate($json);
			}
		} catch (JsonException $e) {
			$request->addError(self::PREFIX . 'messages.invalidJson');
		} catch (NonExistingJsonSchemaException $e) {
			$request->addError(self::PREFIX . 'messages.missingSchema');
		} catch (InvalidJsonException $e) {
			$request->addError(new NotTranslate($e->getMessage()));
		}
		$this->presenter->redrawControl('form');
	}

	/**
	 * Sends IQRF JSON API request
	 * @param Form $form Send IQRF JSON request form
	 */
	public function send(Form $form): void {
		$json = Json::decode($form->getValues()->json);
		try {
			$this->request->set($json);
			$response = $this->wsClient->sendSync($this->request, false);
			$this->presenter->handleShowResponse($response);
			$rsp = new ApiResponse();
			$rsp->set(Json::encode($response['response']));
			$rsp->checkStatus();
			$this->presenter->flashSuccess('iqrfnet.send-json.messages.success');
		} catch (EmptyResponseException $e) {
			$this->presenter->flashError('iqrfnet.webSocketClient.messages.emptyResponse');
		} catch (DpaErrorException | JsonException $e) {
			$this->presenter->flashError('iqrfnet.send-json.messages.failure');
		}
	}

}
