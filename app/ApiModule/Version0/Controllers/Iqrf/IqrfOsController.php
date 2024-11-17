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

namespace App\ApiModule\Version0\Controllers\Iqrf;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ServerErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\IqrfNetModule\Exceptions\DpaFileNotFoundException;
use App\IqrfNetModule\Exceptions\DpaRfMissingException;
use App\IqrfNetModule\Exceptions\UploaderFileException;
use App\IqrfNetModule\Exceptions\UploaderMissingException;
use App\IqrfNetModule\Exceptions\UploaderSpiException;
use App\IqrfNetModule\Models\IqrfOsManager;
use GuzzleHttp\Exception\ClientException;
use Nette\IOException;

/**
 * IQRF OS controller
 */
#[Path('/')]
class IqrfOsController extends BaseIqrfController {

	/**
	 * Constructor
	 * @param IqrfOsManager $iqrfOsManager IQRF OS manager
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly IqrfOsManager $iqrfOsManager,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/osPatches')]
	#[Method('GET')]
	#[OpenApi('
		summary: Lists all IQRF OS patches
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							type: array
							items:
								$ref: \'#/components/schemas/IqrfOsPatchDetail\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
	')]
	public function listOsPatches(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['iqrf:upload']);
		$patches = $this->iqrfOsManager->listOsPatches();
		$response = $response->writeJsonBody($patches);
		return $this->validators->validateResponse('iqrfOsPatchDetail', $response);
	}

	#[Path('/osUpgrades')]
	#[Method('POST')]
	#[OpenApi('
		summary: Lists all IQRF OS upgrades
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/IqrfOsPatchUpgrade\'
		responses:
			\'200\':
				description: Success
				content:
					application/json:
						schema:
							$ref: \'#/components/schemas/IqrfOsUpgradeList\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
	')]
	public function listOsUpgrades(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['iqrf:upload']);
		$this->validators->validateRequest('iqrfOsPatchUpgrade', $request);
		$data = $request->getJsonBodyCopy(false);
		$upgrades = $this->iqrfOsManager->listOsUpgrades($data->build, $data->mcuType);
		$response = $response->writeJsonBody($upgrades);
		return $this->validators->validateResponse('iqrfOsUpgradeList', $response);
	}

	#[Path('/upgradeOs')]
	#[Method('POST')]
	#[OpenApi('
		summary: Upgrades OS and DPA
		requestBody:
			required: true
			content:
				application/json:
					schema:
						$ref: \'#/components/schemas/IqrfOsDpaUpgrade\'
		responses:
			\'200\':
				description: Success
			\'400\':
				$ref: \'#/components/responses/BadRequest\'
			\'403\':
				$ref: \'#/components/responses/Forbidden\'
			\'404\':
				$ref: \'#/components/responses/NotFound\'
			\'500\':
				$ref: \'#/components/responses/ServerError\'
	')]
	public function upgradeOs(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->validators->checkScopes($request, ['iqrf:upload']);
		$this->validators->validateRequest('iqrfOsDpaUpgrade', $request);
		try {
			$this->iqrfOsManager->upgradeOs($request->getJsonBodyCopy(false));
			return $response;
		} catch (DpaRfMissingException | DpaFileNotFoundException | UploaderFileException $e) {
			throw new ClientErrorException($e->getMessage(), ApiResponse::S400_BAD_REQUEST);
		} catch (UploaderMissingException | UploaderSpiException $e) {
			throw new ServerErrorException($e->getMessage(), ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (IOException) {
			throw new ServerErrorException('Filesystem failure', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		} catch (ClientException) {
			throw new ServerErrorException('Failed to download upgrade file', ApiResponse::S500_INTERNAL_SERVER_ERROR);
		}
	}

}
