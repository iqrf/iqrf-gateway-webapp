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

import {mockedAxios, mockedClient} from '../mocks/axios';

import {FeatureService} from '../../services';
import {Feature, type FeatureConfig, type Features} from '../../types';

describe('FeatureService', (): void => {

	/**
	 * @var {FeatureService} service Feature service
	 */
	const service: FeatureService = new FeatureService(mockedClient);

	afterEach((): void => {
		jest.clearAllMocks();
	});

	it('fetch all features', async (): Promise<void> => {
		expect.assertions(3);
		mockedAxios.get.mockResolvedValue({
			data: {
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
			},
		});
		const actual: Features = await service.fetchAll();
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
		expect(mockedAxios.get).toHaveBeenCalledTimes(1);
		expect(mockedAxios.get).toHaveBeenCalledWith('/features');
	});

	it('fetch feature config by its name', async (): Promise<void> => {
		expect.assertions(3);
		mockedAxios.get.mockResolvedValue({
			data: {
				'enabled': true,
				'url': 'https://docs.iqrf.org/iqrf-gateway',
			},
		});
		const actual: FeatureConfig = await service.getConfig(Feature.docs);
		expect(actual).toStrictEqual({
			'enabled': true,
			'url': 'https://docs.iqrf.org/iqrf-gateway',
		});
		expect(mockedAxios.get).toHaveBeenCalledTimes(1);
		expect(mockedAxios.get).toHaveBeenCalledWith('/features/docs');
	});

	it('set feature config', async (): Promise<void> => {
		expect.assertions(2);
		mockedAxios.put.mockResolvedValue({
			data: {
				'enabled': true,
				'url': 'https://docs.iqrf.org/iqrf-gateway',
			},
		});
		await service.setConfig(Feature.docs, {
			'enabled': true,
			'url': 'https://docs.iqrf.org/iqrf-gateway',
		});
		expect(mockedAxios.put).toHaveBeenCalledTimes(1);
		expect(mockedAxios.put).toHaveBeenCalledWith('/features/docs', {
			'enabled': true,
			'url': 'https://docs.iqrf.org/iqrf-gateway',
		});
	});

});
