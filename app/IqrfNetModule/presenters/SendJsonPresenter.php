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

namespace App\IqrfNetModule\Presenters;

use App\CoreModule\Presenters\ProtectedPresenter;
use App\IqrfNetModule\Forms\SendJsonFormFactory;
use Nette\Forms\Form;
use Nette\Utils\Json;

/**
 * Send raw JSON DPA request presenter
 */
class SendJsonPresenter extends ProtectedPresenter {

	/**
	 * @var SendJsonFormFactory Send raw JSON DPA request form
	 * @inject
	 */
	public $sendJsonFactory;

	/**
	 * AJAX handler for showing DPA request and response
	 * @param mixed[] $data DPA request and response
	 */
	public function handleShowResponse(array $data): void {
		foreach ($data as &$json) {
			$json = Json::encode($json, Json::PRETTY);
		}
		$this->template->json = $data;
		$this->redrawControl('responseChange');
	}

	/**
	 * Create send raw JSON DPA request form
	 * @return Form Send raw JSON DPA request form
	 */
	protected function createComponentSendJsonForm(): Form {
		return $this->sendJsonFactory->create($this);
	}

}
