<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

namespace App\ApiModule\Version0\Models;

use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\RequestAttributes;
use App\CoreModule\Exceptions\FeatureNotFoundException;
use App\CoreModule\Models\FeatureManager;
use App\Models\Database\Entities\User;

/**
 * Controller validators
 */
class ControllerValidators {

	/**
	 * Constructor
	 * @param RestApiSchemaValidator $validator REST API JSON schema validator
	 * @param FeatureManager $featureManager Feature manager
	 */
	public function __construct(
		protected readonly RestApiSchemaValidator $validator,
		protected readonly FeatureManager $featureManager,
	) {
	}

	/**
	 * Checks the features
	 * @param array<string>|string $features Required features
	 */
	public function checkFeatures(array|string $features): void {
		if (is_string($features)) {
			$features = [$features];
		}
		foreach ($features as $feature) {
			try {
				if (!$this->featureManager->isEnabled($feature)) {
					throw new ClientErrorException('The feature is not enabled.', ApiResponse::S403_FORBIDDEN);
				}
			} catch (FeatureNotFoundException) {
				throw new ClientErrorException('The feature is not enabled.', ApiResponse::S403_FORBIDDEN);
			}
		}
	}

	/**
	 * Checks the scopes
	 * @param ApiRequest $request API request
	 * @param array<string> $scopes Supported scopes
	 */
	public function checkScopes(ApiRequest $request, array $scopes): void {
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		if ($user instanceof User && array_intersect($scopes, $user->getScopes()) === []) {
			throw new ClientErrorException('Insufficient permissions.', ApiResponse::S403_FORBIDDEN);
		}
	}

	/**
	 * Checks if the request is only for users
	 * @param ApiRequest $request API request
	 */
	public function onlyForUsers(ApiRequest $request): void {
		$user = $request->getAttribute(RequestAttributes::APP_LOGGED_USER);
		if (!$user instanceof User) {
			throw new ClientErrorException('API key is used.', ApiResponse::S403_FORBIDDEN);
		}
	}

	/**
	 * Validates REST API request
	 * @param string $schema REST API JSON schema name
	 * @param ApiRequest $request REST API request
	 * @throws ClientErrorException
	 */
	public function validateRequest(string $schema, ApiRequest $request): void {
		$this->validator->validateRequest($schema, $request);
	}

	/**
	 * Validates REST API response
	 * @param string $schema REST API JSON schema name
	 * @param ApiResponse $response REST API request
	 * @return ApiResponse Validated REST API response
	 */
	public function validateResponse(string $schema, ApiResponse $response): ApiResponse {
		return $this->validator->validateResponse($schema, $response);
	}

}
