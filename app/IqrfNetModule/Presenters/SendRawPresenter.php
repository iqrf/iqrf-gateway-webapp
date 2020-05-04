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
use App\IqrfNetModule\Forms\SendRawFormFactory;
use Iqrf\IdeMacros\MacroFileParser;
use Nette\Application\UI\Form;

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
	 * @var MacroFileParser IQRF IDE Macros parser
	 */
	private $macroParser;

	/**
	 * Constructor
	 * @param MacroFileParser $macroParser IQRF IDE Macros file parser
	 */
	public function __construct(MacroFileParser $macroParser) {
		$this->macroParser = $macroParser;
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
	 */
	public function handleShowResponse(array $data): void {
		$this->template->json = $data;
		$this->redrawControl('responseWrapper');
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
