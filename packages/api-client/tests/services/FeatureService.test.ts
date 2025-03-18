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

import { beforeEach, describe, expect, test } from 'vitest';

import { FeatureService } from '../../src/services';
import { Feature, type FeatureConfig, type Features } from '../../src/types';
import { mockedAxios, mockedClient } from '../mocks/axios';

describe('FeatureService', (): void => {

	/**
	 * @var {FeatureService} service Feature service
	 */
	const service: FeatureService = new FeatureService(mockedClient);

	beforeEach((): void => {
		mockedAxios.reset();
	});

	test('fetch all features', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/features')
			.reply(200, {
				'apcupsd': {
					'enabled': false,
				},
				'docs': {
					'enabled': true,
					'url': 'https://docs.iqrf.org/iqrf-gateway',
				},
				'gatewayPass': {
					'enabled': false,
					'user': 'root',
				},
				'grafana': {
					'enabled': false,
					'url': '/grafana/',
				},
			});
		const actual: Features = await service.list();
		expect(actual).toStrictEqual({
			'apcupsd': {
				'enabled': false,
			},
			'docs': {
				'enabled': true,
				'url': 'https://docs.iqrf.org/iqrf-gateway',
			},
			'gatewayPass': {
				'enabled': false,
				'user': 'root',
			},
			'grafana': {
				'enabled': false,
				'url': '/grafana/',
			},
		});
	});

	test('fetch feature config by its name', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/features/docs')
			.reply(200, {
				'enabled': true,
				'url': 'https://docs.iqrf.org/iqrf-gateway',
			});
		const actual: FeatureConfig = await service.getConfig(Feature.docs);
		expect(actual).toStrictEqual({
			'enabled': true,
			'url': 'https://docs.iqrf.org/iqrf-gateway',
		});
	});

	test('set feature config', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onPut('/features/docs', {
			'enabled': true,
			'url': 'https://docs.iqrf.org/iqrf-gateway',
		})
			.reply(200);
		await service.updateConfig(Feature.docs, {
			'enabled': true,
			'url': 'https://docs.iqrf.org/iqrf-gateway',
		});
	});

});
