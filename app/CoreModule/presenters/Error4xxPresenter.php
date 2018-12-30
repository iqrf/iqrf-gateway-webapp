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

namespace App\CoreModule\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\Request;

/**
 * Error 4xx presenter
 */
class Error4xxPresenter extends BasePresenter {

	/**
	 * Start up presenter
	 * @throws BadRequestException
	 */
	public function startup(): void {
		parent::startup();
		if (!$this->getRequest()->isMethod(Request::FORWARD)) {
			$this->error();
		}
	}

	/**
	 * Render 4xx error page
	 * @param BadRequestException $exception Bad HTTP(S) request exception
	 */
	public function renderDefault(BadRequestException $exception): void {
		// load template 403.latte or 404.latte or ... 4xx.latte
		$directory = __DIR__ . '/../templates/Error/';
		$file = $directory . $exception->getCode() . '.latte';
		$template = is_file($file) ? $file : $directory . '4xx.latte';
		$this->template->setFile($template);
	}

}
