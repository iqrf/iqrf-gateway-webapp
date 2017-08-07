<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
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

namespace App\Presenters;

use App\Forms\IqrfAppSendRawFormFactory;
use App\Model\IqrfAppManager;
use App\Model\IqrfAppParser;
use App\Model\IqrfMacroManager;
use Nette\Application\UI\Form;

/**
 * Service presenter
 */
class IqrfAppPresenter extends BasePresenter {

	/**
	 * @var IqrfAppSendRawFormFactory
	 * @inject
	 */
	public $iqrfAppSendRawFactory;

	/**
	 * @var IqrfAppManager
	 * @inject
	 */
	private $iqrfAppManager;

	/**
	 * @var IqrfAppParser
	 * @inject
	 */
	private $iqrfAppParser;

	/**
	 * @var IqrfMacroManager
	 * @inject
	 */
	private $iqrfMacroManager;

	/**
	 * Constructor
	 * @param IqrfAppManager $manager
	 * @param IqrfAppParser $parser
	 * @param IqrfMacroManager $macroManager
	 */
	public function __construct(IqrfAppManager $manager, IqrfAppParser $parser, IqrfMacroManager $macroManager) {
		$this->iqrfAppManager = $manager;
		$this->iqrfAppParser = $parser;
		$this->iqrfMacroManager = $macroManager;
	}

	/**
	 * Render default page
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
	}

	/**
	 * Render change operational mode page
	 */
	public function renderChangeMode() {
		$this->onlyForAdmins();
	}

	/**
	 * Render send raw DPA packet page
	 */
	public function renderSendRaw() {
		$this->onlyForAdmins();
		$this->template->macros = $this->iqrfMacroManager->read();
	}

	/**
	 * Change IQRF Daemon mode to Forwarding mode
	 */
	public function actionModeForwarding() {
		$this->onlyForAdmins();
		$mode = 'forwarding';
		$this->iqrfAppManager->changeOperationMode($mode);
		$this->flashMessage('IQRF Daemon mode has been changed to ' . $mode . ' mode.', 'info');
		$this->redirect('IqrfApp:changeMode');
		$this->setView('changeMode');
	}

	/**
	 * Change IQRF Daemon mode to Operational mode
	 */
	public function actionModeOperational() {
		$this->onlyForAdmins();
		$mode = 'operational';
		$this->iqrfAppManager->changeOperationMode($mode);
		$this->flashMessage('IQRF Daemon mode has been changed to ' . $mode . ' mode.', 'info');
		$this->redirect('IqrfApp:changeMode');
		$this->setView('changeMode');
	}

	/**
	 * Change IQRF Daemon mode to Service mode
	 */
	public function actionModeService() {
		$this->onlyForAdmins();
		$mode = 'service';
		$this->iqrfAppManager->changeOperationMode($mode);
		$this->flashMessage('IQRF Daemon mode has been changed to ' . $mode . ' mode.', 'info');
		$this->redirect('IqrfApp:changeMode');
		$this->setView('changeMode');
	}

	/**
	 * AJAX handler for showing DPA response
	 * @param string $response DPA response
	 */
	public function handleShowResponse($response) {
		$this->template->response = $response;
		$this->template->parsedResponse = $this->iqrfAppParser->parseResponse($response);
		$this->redrawControl('responseChange');
	}

	/**
	 * Create send raw DPA packet form
	 * @return Form
	 */
	protected function createComponentIqrfAppSendRawForm() {
		$this->onlyForAdmins();
		return $this->iqrfAppSendRawFactory->create($this);
	}

}
