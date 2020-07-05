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

namespace App\ApiModule\Version0\Controllers;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\RequestParameters;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\CoreModule\Models\FeatureManager;

/**
 * Optional feature manager controller
 * @Path("/features")
 * @Tag("Optional feature manager")
 */
class FeatureController extends BaseController {

	/**
	 * @var FeatureManager Optional feature manager
	 */
	private $manager;

	/**
	 * onstructor
	 * @param FeatureManager $manager Optional feature manager
	 */
	public function __construct(FeatureManager $manager) {
		$this->manager = $manager;
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns optional features configuration
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/FeatureList'
	 *      '500':
	 *          description: Server error
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function getAll(ApiRequest $request, ApiResponse $response): ApiResponse {
		$config = $this->manager->read();
		return $response->writeJsonBody($config);
	}

	/**
	 * @Path("/{feature}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns optional feature configuration
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/Feature'
	 *      '404':
	 *          description: Feature not found
	 *      '500':
	 *          description: Server error
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="feature", type="string", description="Feature name")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = urldecode($request->getParameter('feature'));
		$config = $this->manager->read();
		if (array_key_exists($name, $config)) {
			return $response->writeJsonBody($config[$name]);
		}
		return $response->withStatus(404, 'Feature not found');
	}

}
