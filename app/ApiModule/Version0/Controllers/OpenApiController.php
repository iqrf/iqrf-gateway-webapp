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

namespace App\ApiModule\Version0\Controllers;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\ApiModule\Version0\Models\ControllerValidators;
use App\ApiModule\Version0\Models\OpenApiSchemaBuilder;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;

/**
 * OpenAPI controller
 */
#[Path('/openapi')]
#[Tag('OpenAPI')]
class OpenApiController extends BaseController {

	/**
	 * Constructor
	 * @param OpenApiSchemaBuilder $schemaBuilder OpenAPI schema builder
	 * @param ControllerValidators $validators Controller validators
	 */
	public function __construct(
		private readonly OpenApiSchemaBuilder $schemaBuilder,
		ControllerValidators $validators,
	) {
		parent::__construct($validators);
	}

	#[Path('/')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns OpenAPI schema
		security:
			- []
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/OpenApiSpecification'
	EOT)]
	public function index(ApiRequest $request, ApiResponse $response): ApiResponse {
		return $response->writeJsonBody($this->schemaBuilder->getArray());
	}

	#[Path('/schemas/{name}')]
	#[Method('GET')]
	#[OpenApi(<<<'EOT'
		summary: Returns JSON schema
		security:
			- []
		responses:
			'200':
				description: Success
				content:
					application/json:
						schema:
							$ref: '#/components/schemas/JsonSchema'
			'404':
				description: Not found
	EOT)]
	#[RequestParameter(name: 'name', type: 'string', in: 'path', required: true, description: 'Name of schema')]
	public function getSchema(ApiRequest $request, ApiResponse $response): ApiResponse {
		$name = $request->getParameter('name');
		$path = __DIR__ . '/../../../../api/schemas/' . $name . '.json';
		$baseUrl = Strings::replace((string) $request->getUri(), '~' . $name . '$~');
		try {
			$json = Json::decode(FileSystem::read($path));
			array_walk_recursive(
				$json,
				function (mixed &$value, string $key) use ($baseUrl): void {
					if (!in_array($key, ['$id', '$ref'], true)) {
						return;
					}
					$matches = Strings::match($value, '~^https://apidocs\.iqrf\.org/openapi/iqrf-gateway-webapp/schemas/(?<name>.*)\.json$~');
					if ($matches === null) {
						return;
					}
					$value = $baseUrl . $matches['name'];
				}
			);
			return $response->writeBody(Json::encode($json))
				->withHeader('Content-Type', 'application/json')
				->withStatus(ApiResponse::S200_OK);
		} catch (IOException) {
			throw new ClientErrorException('Schema not found', ApiResponse::S404_NOT_FOUND);
		}
	}

}
