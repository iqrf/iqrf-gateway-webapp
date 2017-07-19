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

use App\Forms;
use App\Model\IqrfAppManager;
use App\Model\IqrfMacroManager;

use Tracy\Debugger;

/**
 * Service presenter
 */
class IqrfAppPresenter extends BasePresenter {

	/**
	 * @var Forms\IqrfAppSendRawFormFactory
	 * @inject
	 */
	public $iqrfAppSendRawFactory;

	/**
	 * @var IqrfAppManager
	 * @inject
	 */
	private $iqrfAppManager;

	/**
	 * @var IqrfMacroManager
	 * @inject
	 */
	private $iqrfMacroManager;

	/**
	 * @param IqrfAppManager $iqrfAppManager
	 */
	public function __construct(IqrfAppManager $iqrfAppManager, IqrfMacroManager $iqrfMacroManager) {
		$this->iqrfAppManager = $iqrfAppManager;
		$this->iqrfMacroManager = $iqrfMacroManager;
	}

	public function renderDefault() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		$this->template->macros = $this->iqrfMacroManager->read();
	}

	public function handleShowResponse($response) {
		$this->template->response = $response;
		$this->redrawControl('responseChange');
	}

	protected function createComponentIqrfAppSendRawForm() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		return $this->iqrfAppSendRawFactory->create($this);
	}

}
