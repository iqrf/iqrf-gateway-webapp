<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
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

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\BadRequestException;
use Nette\Application\Helpers;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\Responses\ForwardResponse;
use Tracy\ILogger;

/**
 * Error presenter
 */
class ErrorPresenter implements IPresenter {

	use Nette\SmartObject;

	/** @var ILogger */
	private $logger;

	/**
	 * Constructor
	 * @param ILogger $logger
	 */
	public function __construct(ILogger $logger) {
		$this->logger = $logger;
	}

	/**
	 * Run Error presenter
	 * @param Request $request
	 * @return ForwardResponse|CallbackResponse
	 */
	public function run(Request $request) {
		$exception = $request->getParameter('exception');
		if ($exception instanceof BadRequestException) {
			list($module,, $sep) = Helpers::splitName($request->getPresenterName());
			return new ForwardResponse($request->setPresenterName($module . $sep . 'Error4xx'));
		}

		$this->logger->log($exception, ILogger::EXCEPTION);
		return new CallbackResponse(function () {
			require __DIR__ . '/../templates/Error/500.phtml';
		});
	}

}
