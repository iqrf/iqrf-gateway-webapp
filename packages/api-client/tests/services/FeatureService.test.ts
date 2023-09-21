/**
 * Copyright 2023 MICRORISC s.r.o.
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
import {describe, expect, it} from 'vitest';

import {mockedAxios, mockedClient} from '../mocks/axios';

import {FeatureService} from '../../src/services';
import {Feature, type FeatureConfig, type Features} from '../../src/types';

describe('FeatureService', (): void => {

	/**
	 * @var {FeatureService} service Feature service
	 */
	const service: FeatureService = new FeatureService(mockedClient);

	it('fetch all features', async (): Promise<void> => {
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
		await service.fetchAll()
			.then((actual: Features): void => {
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
	});

	it('fetch feature config by its name', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/features/docs')
			.reply(200, {
				'enabled': true,
				'url': 'https://docs.iqrf.org/iqrf-gateway',
			});
		await service.getConfig(Feature.docs)
			.then((actual: FeatureConfig): void => {
				expect(actual).toStrictEqual({
					'enabled': true,
					'url': 'https://docs.iqrf.org/iqrf-gateway',
				});
			});
	});

	it('set feature config', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onPut('/features/docs', {
			'enabled': true,
			'url': 'https://docs.iqrf.org/iqrf-gateway',
		})
			.reply(200);
		await service.setConfig(Feature.docs, {
			'enabled': true,
			'url': 'https://docs.iqrf.org/iqrf-gateway',
		});
	});

});
