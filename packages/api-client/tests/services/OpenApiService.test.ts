/**
 * Copyright 2023-2025 MICRORISC s.r.o.
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

import { type OpenAPI3, type ServerObject } from 'openapi-typescript';
import { beforeEach, describe, expect, test } from 'vitest';

import { OpenApiService } from '../../src/services';
import { mockedAxios, mockedClient } from '../mocks/axios';

describe('OpenApiService', (): void => {

	/**
	 * @var {OpenApiService} service OpenAPI specification service
	 */
	const service: OpenApiService = new OpenApiService(mockedClient);

	/**
	 * @var {OpenAPI3} specification OpenAPI specification
	 */
	const specification: OpenAPI3 = {
		'openapi': '3.0.2',
		'info': {
			'title': 'IQRF Gateway Webapp API specification',
			'contact': {
				'name': 'Roman Ondráček',
				'email': 'roman.ondracek@iqrf.com',
			},
			'license': {
				'identifier': 'Apache-2.0',
				'name': 'Apache 2.0',
				'url': 'https://www.apache.org/licenses/LICENSE-2.0.html',
			},
			'version': '0.0.1',
		},
		'paths': {
			'/openapi': {
				'get': {
					'tags': ['OpenAPI'],
					'summary': 'Returns OpenAPI schema',
					'security': [{}],
					'responses': {
						'200': {
							'description': 'Success',
							'content': {
								'application/json': {
									'schema': { '$ref': '#/components/schemas/OpenApiSpecification' },
								},
							},
						},
					},
				},
			},
		},
		'servers': [
			{
				'url': '{protocol}://{server}/api/v0/',
				'variables': {
					'protocol': {
						'enum': ['http', 'https'],
						'default': 'http',
					},
					'server': {
						'default': 'localhost:8080',
					},
				},
			} as unknown as ServerObject,
		],
	};

	beforeEach((): void => {
		mockedAxios.reset();
	});

	test('fetch OpenAPI specification', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/openapi')
			.reply(200, specification);
		const actual: OpenAPI3 = await service.getSpecification('http://localhost:8080/api/v0/');
		expect(actual).toStrictEqual({
			...specification,
			servers: [
				{
					'url': 'http://localhost:8080/api/v0/',
				},
			],
		});
	});

});
