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

import {mockedAxios, mockedClient} from '../../mocks/axios';

import {IqrfGatewayControllerService} from '../../../src/services/Config';
import {
	type IqrfGatewayControllerConfig,
	IqrfGatewayControllerLoggingSeverity,
	IqrfGatewayControllerAction,
	type IqrfGatewayControllerMapping,
	IqrfGatewayControllerMappingDevice,
} from '../../../src/types/Config';

describe('IqrfGatewayControllerService', (): void => {

	/**
	 * @var {IqrfGatewayControllerService} service IQRF Gateway Controller service
	 */
	const service: IqrfGatewayControllerService = new IqrfGatewayControllerService(mockedClient);

	/**
	 * @var {IqrfGatewayControllerConfig} configuration IQRF Gateway Controller configuration
	 */
	const configuration: IqrfGatewayControllerConfig = {
		daemonApi: {
			autoNetwork: {
				actionRetries: 1,
				discoveryBeforeStart: false,
				discoveryTxPower: 7,
				returnVerbose: true,
				skipDiscoveryEachWave: false,
				stopConditions: {
					abortOnTooManyNodesFound: true,
					emptyWaves: 2,
					waves: 2,
				},
			},
			discovery: {
				maxAddr: 239,
				returnVerbose: true,
				txPower: 7,
			},
		},
		factoryReset: {
			coordinator: false,
			daemon: false,
			network: true,
			webapp: true,
			iqaros: true,
		},
		logger: {
			filePath: '/var/log/iqrf-gateway-controller.log',
			severity: IqrfGatewayControllerLoggingSeverity.Error,
			sinks: {
				file: true,
				syslog: true,
			},
		},
		powerOff: {
			sck: -1,
			sda: -1,
		},
		resetButton: {
			api: IqrfGatewayControllerAction.None,
			button: 2,
		},
		statusLed: {
			greenLed: 0,
			redLed: 1,
		},
		wsServers: {
			api: 'ws://localhost:1338',
			monitor: 'ws://localhost:1438',
		},
	};

	/**
	 * @var {IqrfGatewayControllerMapping} profile Device configuration profile
	 */
	const profile: IqrfGatewayControllerMapping = {
		button: 2,
		deviceType: IqrfGatewayControllerMappingDevice.Board,
		greenLed: 0,
		redLed: 1,
		name: 'Test',
		id: 1,
		sck: -1,
		sda: -1,
	};

	it('fetch IQRF Gateway Controller config', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/config/controller')
			.reply(200, configuration);
		await service.fetchConfig()
			.then((actual: IqrfGatewayControllerConfig): void => {
				expect(actual).toStrictEqual(configuration);
			});
	});

	it('update IQRF Gateway Controller config', async (): Promise<void> => {
		expect.assertions(0);
		const config: IqrfGatewayControllerConfig = {
			...configuration,
			resetButton: {
				...configuration.resetButton,
				api: IqrfGatewayControllerAction.Discovery,
			},
		};
		mockedAxios.onPut('/config/controller', config)
			.reply(200);
		await service.saveConfig(config);
	});

	it('list IQRF Gateway Controller mappings', async (): Promise<void> => {
		expect.assertions(1);
		const profiles: IqrfGatewayControllerMapping[] = [profile];
		mockedAxios.onGet('/config/controller/pins')
			.reply(200, profiles);
		await service.listMappings()
			.then((actual: IqrfGatewayControllerMapping[]): void => {
				expect(actual).toStrictEqual(profiles);
			});
	});

	it('fetch IQRF Gateway Controller mapping', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/config/controller/pins/1')
			.reply(200, profile);
		await service.fetchMapping(1)
			.then((actual: IqrfGatewayControllerMapping): void => {
				expect(actual).toStrictEqual(profile);
			});
	});

	it('create IQRF Gateway Controller mapping', async (): Promise<void> => {
		expect.assertions(1);
		const expected = {...profile};
		delete expected.id;
		mockedAxios.onPost('/config/controller/pins', profile)
			.reply(function (config) {
				expect(expected).toEqual(JSON.parse(config.data));
				return Promise.resolve([201]);
			});
		await service.createMapping(profile);
	});

	it('delete IQRF Gateway Controller mapping', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onDelete('/config/controller/pins/1')
			.reply(200);
		await service.deleteMapping(1);
	});

	it('edit IQRF Gateway Controller mapping', async(): Promise<void> => {
		expect.assertions(0);
		const data = {...profile};
		data.deviceType = IqrfGatewayControllerMappingDevice.Adapter;
		mockedAxios.onPut('/config/controller/pins/1', data)
			.reply(200);
		await service.editMapping(1, data);
	});
});
