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

namespace App\Forms;

use App\Forms\FormFactory;
use App\Model\IqrfAppManager;
use App\Presenters\IqrfAppPresenter;
use Nette;
use Nette\Application\UI\Form;

class IqrfAppSendRawFormFactory {

	use Nette\SmartObject;

	/**
	 * @var IqrfAppManager
	 */
	private $iqrfAppManager;

	/**
	 * @var FormFactory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param FormFactory $factory
	 * @param IqrfAppManager $iqrfAppManager
	 */
	public function __construct(FormFactory $factory, IqrfAppManager $iqrfAppManager) {
		$this->factory = $factory;
		$this->iqrfAppManager = $iqrfAppManager;
	}

	/**
	 * Create IQRF App send RAW packet form
	 * @param IqrfAppPresenter $presenter
	 * @return Form IQRF App send RAW packet form
	 */
	public function create(IqrfAppPresenter $presenter) {
		$form = $this->factory->create();
		$form->addText('packet', 'Raw IQRF packet')->setRequired();
		$form->addSubmit('send', 'Send');
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			$packet = $values['packet'];
			if ($this->iqrfAppManager->validatePacket($packet)) {
				$response = $this->iqrfAppManager->sendRaw($packet);
				$presenter->handleShowResponse($response);
			}
		};
		return $form;
	}

}
