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

import {ServiceService} from '../../services';
import {type ServiceStatus} from '../../types';

describe('ServiceService', (): void => {

	/**
	 * @var {ServiceService} service System service service
	 */
	const service: ServiceService = new ServiceService(mockedClient);

	/**
	 * @var {string} serviceName Service name
	 */
	const serviceName = 'iqrf-gateway-daemon';

	afterEach((): void => {
		jest.clearAllMocks();
	});

	it('fetch list of supported system services', async (): Promise<void> => {
		expect.assertions(3);
		const services: string[] = ['iqrf-gateway-daemon'];
		mockedAxios.get.mockResolvedValue({data: {services: services}});
		const actual: string[] = await service.list();
		expect(actual).toStrictEqual(services);
		expect(mockedAxios.get).toHaveBeenCalledTimes(1);
		expect(mockedAxios.get).toHaveBeenCalledWith('/services');
	});

	it('fetch status of `iqrf-gateway-daemon` service', async (): Promise<void> => {
		expect.assertions(3);
		const status: ServiceStatus = {
			'active': true,
			'enabled': true,
			'status': '● iqrf-gateway-daemon.service - IQRF Gateway Daemon\n     Loaded: loaded (/etc/systemd/system/iqrf-gateway-daemon.service; enabled; preset: enabled)\n     Active: active (running) since Sat 2023-07-08 01:14:30 CEST; 2 days ago\n       Docs: man:iqrfgd2(1)\n             https://docs.iqrf.org/iqrf-gateway/\n   Main PID: 1252755 (iqrfgd2)\n      Tasks: 17 (limit: 76968)\n     Memory: 6.1M\n        CPU: 15.050s\n     CGroup: /system.slice/iqrf-gateway-daemon.service\n             └─1252755 /usr/bin/iqrfgd2 /etc/iqrf-gateway-daemon/config.json\n\nJul 08 01:14:41 ASRock-X570-Extreme4 iqrfgd2[1252755]: clibspi_gpio_setup() setDir failed wait for 100 ms to next try: 10\nJul 08 01:14:41 ASRock-X570-Extreme4 iqrfgd2[1252755]: Error during opening file: No such file or directory\nJul 08 01:14:41 ASRock-X570-Extreme4 iqrfgd2[1252755]: Error during opening file: No such file or directory\nJul 08 01:14:41 ASRock-X570-Extreme4 iqrfgd2[1252755]: Error: Cannot get TR reset msg => interface to DPA coordinator is not working - verify (CDC or SPI or UART) configuration\nJul 08 01:14:43 ASRock-X570-Extreme4 iqrfgd2[1252755]: Error: Cannot get TR parameters msg => interface to DPA coordinator is not working - verify (CDC or SPI or UART) configuration\nJul 08 01:14:43 ASRock-X570-Extreme4 iqrfgd2[1252755]: Error: Interface to DPA coordinator is not ready - verify (CDC or SPI or UART) configuration\nJul 08 01:14:43 ASRock-X570-Extreme4 iqrfgd2[1252755]: Loading IqrfRepo cache ...\nJul 08 01:14:44 ASRock-X570-Extreme4 iqrfgd2[1252755]: Loading IqrfRepo cache success\nJul 08 01:14:44 ASRock-X570-Extreme4 iqrfgd2[1252755]: Cannot load required package for: os="0000" dpa="0000"\nJul 08 01:14:44 ASRock-X570-Extreme4 iqrfgd2[1252755]: Loaded package for: os="08B1" dpa="0300"',
		};
		mockedAxios.get.mockResolvedValue({data: status});
		const actual: ServiceStatus = await service.getStatus(serviceName);
		expect(actual).toStrictEqual(status);
		expect(mockedAxios.get).toHaveBeenCalledTimes(1);
		expect(mockedAxios.get).toHaveBeenCalledWith('/services/' + serviceName);
	});

	it('enable `iqrf-gateway-daemon` service', async (): Promise<void> => {
		expect.assertions(2);
		mockedAxios.post.mockResolvedValue({data: null});
		await service.enable(serviceName);
		expect(mockedAxios.post).toHaveBeenCalledTimes(1);
		expect(mockedAxios.post).toHaveBeenCalledWith('/services/' + serviceName + '/enable');
	});

	it('disable `iqrf-gateway-daemon` service', async (): Promise<void> => {
		expect.assertions(2);
		mockedAxios.post.mockResolvedValue({data: null});
		await service.disable(serviceName);
		expect(mockedAxios.post).toHaveBeenCalledTimes(1);
		expect(mockedAxios.post).toHaveBeenCalledWith('/services/' + serviceName + '/disable');
	});

	it('start `iqrf-gateway-daemon` service', async (): Promise<void> => {
		expect.assertions(2);
		mockedAxios.post.mockResolvedValue({data: null});
		await service.start(serviceName);
		expect(mockedAxios.post).toHaveBeenCalledTimes(1);
		expect(mockedAxios.post).toHaveBeenCalledWith('/services/' + serviceName + '/start');
	});

	it('stop `iqrf-gateway-daemon` service', async (): Promise<void> => {
		expect.assertions(2);
		mockedAxios.post.mockResolvedValue({data: null});
		await service.stop(serviceName);
		expect(mockedAxios.post).toHaveBeenCalledTimes(1);
		expect(mockedAxios.post).toHaveBeenCalledWith('/services/' + serviceName + '/stop');
	});

	it('restart `iqrf-gateway-daemon` service', async (): Promise<void> => {
		expect.assertions(2);
		mockedAxios.post.mockResolvedValue({data: null});
		await service.restart(serviceName);
		expect(mockedAxios.post).toHaveBeenCalledTimes(1);
		expect(mockedAxios.post).toHaveBeenCalledWith('/services/' + serviceName + '/restart');
	});

});
