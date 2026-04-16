<?php

/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

namespace App\ApiModule\Version0\Middlewares;

use Apitte\Core\Http\ApiResponse;
use Contributte\Middlewares\IMiddleware;
use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Service unavailable middleware
 */
class UnavailableMiddleware implements IMiddleware {

	/**
	 * Unavailable paths
	 */
	private const UNAVAILABLE_PATHS = [
		'/api/v0/iqrf/dpaFile' => true,
		'/api/v0/iqrf/osPatches' => true,
		'/api/v0/iqrf/osUpgrades' => true,
		'/api/v0/iqrf/upgradeOs' => true,
		'/api/v0/iqrf/upload' => true,
		'/api/v0/iqrf/uploader' => true,
	];

	/**
	 * Creates unavailable response
	 * @param ResponseInterface $response Response to modify
	 * @param string $message Message
	 * @return ResponseInterface Response
	 */
	private function createUnavailableResponse(ResponseInterface $response, string $message): ResponseInterface {
		$json = Json::encode([
			'status' => 'error',
			'code' => 503,
			'message' => $message,
		]);
		$response->getBody()->write($json);
		return $response->withStatus(ApiResponse::S503_SERVICE_UNAVAILABLE)
			->withHeader('Content-Type', 'application/json');
	}

	/**
	 * Middleware invocation
	 * @param ServerRequestInterface $request Request
	 * @param ResponseInterface $response Response
	 * @param callable(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface $next
	 * @return ResponseInterface Response
	 */
	public function __invoke(
		ServerRequestInterface $request,
		ResponseInterface $response,
		callable $next,
	): ResponseInterface {
		// Get request url
		$requestUrl = rtrim($request->getUri()->getPath(), '/');
		// If path is marked unavailable, stop middleware chain and return unavailable response
		if (isset(self::UNAVAILABLE_PATHS[$requestUrl])) {
			return $this->createUnavailableResponse(
				response: $response,
				message: 'The requested service is unavailable at this time.',
			);
		}
		// Pass to next middleware
		return $next($request, $response);
	}

}
