<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace App\ApiModule\Version0;

use Apitte\Core\Dispatcher\DispatchError;
use Apitte\Core\ErrorHandler\PsrLogErrorHandler;
use Apitte\Core\Http\ApiResponse;

/**
 * REST API error handler
 */
class ErrorHandler extends PsrLogErrorHandler {

	public function handle(DispatchError $dispatchError): ApiResponse {
		$response = parent::handle($dispatchError);
		$request = $dispatchError->getRequest();
		if ($request->getMethod() === 'OPTIONS') {
			return $response->withHeader('Access-Control-Allow-Origin', '*')
				->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, OPTIONS, DELETE')
				->withHeader('Access-Control-Allow-Headers', 'Accept, Content-Type, Authorization, sentry-trace, baggage')
				->withHeader('Access-Control-Expose-Headers', '*');
		}
		return $response->withHeader('Access-Control-Allow-Origin', '*')
			->withHeader('Access-Control-Allow-Headers', 'Accept, Content-Type, Authorization, sentry-trace, baggage')
			->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, DELETE, PUT')
			->withHeader('Access-Control-Expose-Headers', '*');
	}

}
