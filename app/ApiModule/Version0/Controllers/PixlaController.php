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
use Apitte\Core\Annotation\Controller\Response;
use Apitte\Core\Annotation\Controller\Responses;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\CloudModule\Models\PixlaManager;
use App\ServiceModule\Exceptions\NonexistentServiceException;
use Nette\IOException;
use Nette\Utils\JsonException;

/**
 * PIXLA controller
 * @Path("/pixla")
 * @Tag("PIXLA")
 */
class PixlaController extends BaseController {

	/**
	 * @var PixlaManager PIXLA client service manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param PixlaManager $manager PIXLA client service manager
	 */
	public function __construct(PixlaManager $manager) {
		$this->manager = $manager;
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Returns status of PIXLA client service and PIXLA token
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/PixlaStatus'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function status(ApiRequest $request, ApiResponse $response): ApiResponse {
		$status = [
			'token' => $this->manager->getToken(),
		];
		try {
			$status['status'] = $this->manager->getServiceStatus() ? 'enabled' : 'disabled';
		} catch (NonexistentServiceException $e) {
			$status['status'] = 'missing';
		}
		return $response->writeJsonBody($status);
	}

	/**
	 * @Path("/token")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Sets new PIXLA token string
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/PixlaToken'
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="400", description="Bad request"),
	 *      @Response(code="500", description="Server error")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function setToken(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->manager->setToken($request->getJsonBody()['token']);
			return $response;
		} catch (JsonException $e) {
			return $response->withStatus(ApiResponse::S400_BAD_REQUEST, 'Invalid JSON syntax.');
		} catch (IOException $e) {
			return $response->withStatus(ApiResponse::S500_INTERNAL_SERVER_ERROR, $e->getMessage());
		}
	}

	/**
	 * @Path("/enable")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Enables PIXLA client service
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function enable(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->manager->enableService();
		return $response;
	}

	/**
	 * @Path("/disable")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Disables PIXLA client service
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function disable(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->manager->disableService();
		return $response;
	}

}
