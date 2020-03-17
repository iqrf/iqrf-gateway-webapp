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
use App\GatewayModule\Models\SshManager;

/**
 * SSH daemon service controller
 * @Path("/ssh")
 * @Tag("SSH daemon service manager")
 */
class SshController extends BaseController {

	/**
	 * @var SshManager SSH daemon service manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param SshManager $manager SSH daemon service manager
	 */
	public function __construct(SshManager $manager) {
		$this->manager = $manager;
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *   summary: Returns status of SSH daemon service
	 * ")
	 * @Responses({
	 *      @Response(code="200", description="Success")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function status(ApiRequest $request, ApiResponse $response): ApiResponse {
		$status = [
			'status' => $this->manager->getServiceStatus()->toScalar(),
		];
		return $response->writeJsonBody($status);
	}

	/**
	 * @Path("/enable")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Enables SSH daemon service
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
	 *   summary: Disables SSH daemon service
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
