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

import { describe, expect, it } from 'vitest';

import { ServiceService } from '../../src/services';
import { type ServiceState, type ServiceStatus } from '../../src/types';
import { mockedAxios, mockedClient } from '../mocks/axios';

describe('ServiceService', (): void => {

	/**
	 * @var {ServiceService} service System service service
	 */
	const service: ServiceService = new ServiceService(mockedClient);

	/**
	 * @var {string} serviceName Service name
	 */
	const serviceName = 'iqrf-gateway-daemon';

	it('fetch list of supported system services', async (): Promise<void> => {
		expect.assertions(1);
		const services: ServiceState[] = [
			{
				active: true,
				enabled: true,
				name: 'iqrf-gateway-daemon',
				status: null,
			},
		];
		mockedAxios.onGet('/services', {
			params: {
				withStatus: false,
			},
		})
			.reply(200, services);
		await service.list(false)
			.then((actual: ServiceState[]): void => {
				expect(actual).toStrictEqual(services);
			});
	});

	it('fetch status of `iqrf-gateway-daemon` service', async (): Promise<void> => {
		expect.assertions(1);
		const status: ServiceStatus = {
			'active': true,
			'enabled': true,
			'status': '● iqrf-gateway-daemon.service - IQRF Gateway Daemon\n     Loaded: loaded (/etc/systemd/system/iqrf-gateway-daemon.service; enabled; preset: enabled)\n     Active: active (running) since Sat 2023-07-08 01:14:30 CEST; 2 days ago\n       Docs: man:iqrfgd2(1)\n             https://docs.iqrf.org/iqrf-gateway/\n   Main PID: 1252755 (iqrfgd2)\n      Tasks: 17 (limit: 76968)\n     Memory: 6.1M\n        CPU: 15.050s\n     CGroup: /system.slice/iqrf-gateway-daemon.service\n             └─1252755 /usr/bin/iqrfgd2 /etc/iqrf-gateway-daemon/config.json\n\nJul 08 01:14:41 ASRock-X570-Extreme4 iqrfgd2[1252755]: clibspi_gpio_setup() setDir failed wait for 100 ms to next try: 10\nJul 08 01:14:41 ASRock-X570-Extreme4 iqrfgd2[1252755]: Error during opening file: No such file or directory\nJul 08 01:14:41 ASRock-X570-Extreme4 iqrfgd2[1252755]: Error during opening file: No such file or directory\nJul 08 01:14:41 ASRock-X570-Extreme4 iqrfgd2[1252755]: Error: Cannot get TR reset msg => interface to DPA coordinator is not working - verify (CDC or SPI or UART) configuration\nJul 08 01:14:43 ASRock-X570-Extreme4 iqrfgd2[1252755]: Error: Cannot get TR parameters msg => interface to DPA coordinator is not working - verify (CDC or SPI or UART) configuration\nJul 08 01:14:43 ASRock-X570-Extreme4 iqrfgd2[1252755]: Error: Interface to DPA coordinator is not ready - verify (CDC or SPI or UART) configuration\nJul 08 01:14:43 ASRock-X570-Extreme4 iqrfgd2[1252755]: Loading IqrfRepo cache ...\nJul 08 01:14:44 ASRock-X570-Extreme4 iqrfgd2[1252755]: Loading IqrfRepo cache success\nJul 08 01:14:44 ASRock-X570-Extreme4 iqrfgd2[1252755]: Cannot load required package for: os="0000" dpa="0000"\nJul 08 01:14:44 ASRock-X570-Extreme4 iqrfgd2[1252755]: Loaded package for: os="08B1" dpa="0300"',
		};
		mockedAxios.onGet('/services/' + serviceName)
			.reply(200, status);
		await service.getStatus(serviceName)
			.then((actual: ServiceStatus): void => {
				expect(actual).toStrictEqual(status);
			});
	});

	it('enable `iqrf-gateway-daemon` service', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onPost('/services/' + serviceName + '/enable')
			.reply(200);
		await service.enable(serviceName);
	});

	it('disable `iqrf-gateway-daemon` service', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onPost('/services/' + serviceName + '/disable')
			.reply(200);
		await service.disable(serviceName);
	});

	it('start `iqrf-gateway-daemon` service', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onPost('/services/' + serviceName + '/start')
			.reply(200);
		await service.start(serviceName);
	});

	it('stop `iqrf-gateway-daemon` service', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onPost('/services/' + serviceName + '/stop')
			.reply(200);
		await service.stop(serviceName);
	});

	it('restart `iqrf-gateway-daemon` service', async (): Promise<void> => {
		expect.assertions(0);
		mockedAxios.onPost('/services/' + serviceName + '/restart')
			.reply(200);
		await service.restart(serviceName);
	});

});
