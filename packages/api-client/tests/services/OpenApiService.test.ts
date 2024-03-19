/**
 * Copyright 2023-2024 MICRORISC s.r.o.
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

import { beforeEach, describe, expect, it } from 'vitest';

import { OpenApiService } from '../../src/services';
import { mockedAxios, mockedClient } from '../mocks/axios';

describe('OpenApiService', (): void => {

	/**
	 * @var {OpenApiService} service OpenAPI specification service
	 */
	const service: OpenApiService = new OpenApiService(mockedClient);

	/**
	 * @var {object} specification OpenAPI specification
	 */
	const specification = {
		'openapi': '3.0.2',
		'info': {
			'title': 'IQRF Gateway Webapp API specification',
			'contact': {
				'name': 'Roman Ondráček',
				'email': 'roman.ondracek@iqrf.com',
			},
			'license': {
				'name': 'Apache 2.0',
				'url': 'https://www.apache.org/licenses/LICENSE-2.0.html',
			},
			'version':'0.0.1',
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
									'schema': { '$ref':'#/components/schemas/OpenApiSpecification' },
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
						'enum': ['http','https'],
						'default': 'http',
					},
					'server': {
						'default':'localhost:8080',
					},
				},
			},
		],
	};

	beforeEach((): void => {
		mockedAxios.reset();
	});

	it('fetch OpenAPI specification', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/openapi')
			.reply(200, specification);
		await service.fetchSpecification('http://localhost:8080/api/v0/')
			.then((actual: object): void => {
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

});
