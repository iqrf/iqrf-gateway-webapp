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

import {mockedAxios, mockedClient} from '../../mocks/axios';

import {MailerService} from '../../../services';
import {type MailerConfig, MailerSmtpSecurity} from '../../../types';

describe('MailerService', (): void => {

	/**
	 * @var {MailerService} service Mailer configuration service
	 */
	const service: MailerService = new MailerService(mockedClient);

	afterEach((): void => {
		jest.clearAllMocks();
	});

	it('fetch mailer config', async (): Promise<void> => {
		expect.assertions(3);
		const config: MailerConfig = {
			'enabled': false,
			'host': 'localhost',
			'port': 25,
			'username': 'root',
			'password': '',
			'secure': null,
			'from': 'iqrf-gw@localhost.localdomain',
			'theme': 'generic',
			'timeout': 20,
			'context': [],
			'clientHost': null,
			'persistent': false,
		};
		mockedAxios.get.mockResolvedValue({data: config});
		const actual: MailerConfig = await service.getConfig();
		expect(actual).toStrictEqual(config);
		expect(mockedAxios.get).toHaveBeenCalledTimes(1);
		expect(mockedAxios.get).toHaveBeenCalledWith('/config/mailer');
	});

	it('fetch mailer config with IDN', async (): Promise<void> => {
		expect.assertions(3);
		const config: MailerConfig = {
			'enabled': true,
			'host': 'xn--ondrek-sta66a.eu',
			'port': 465,
			'username': 'iqrf-gw',
			'password': 'password',
			'secure': MailerSmtpSecurity.TLS,
			'from': 'iqrf-gw@xn--ondrek-sta66a.eu',
			'theme': 'generic',
			'timeout': 20,
			'context': [],
			'clientHost': null,
			'persistent': false,
		};
		const expected: MailerConfig = {
			'enabled': true,
			'host': 'ondráček.eu',
			'port': 465,
			'username': 'iqrf-gw',
			'password': 'password',
			'secure': MailerSmtpSecurity.TLS,
			'from': 'iqrf-gw@ondráček.eu',
			'theme': 'generic',
			'timeout': 20,
			'context': [],
			'clientHost': null,
			'persistent': false,
		};
		mockedAxios.get.mockResolvedValue({data: config});
		const actual: MailerConfig = await service.getConfig();
		expect(actual).toStrictEqual(expected);
		expect(mockedAxios.get).toHaveBeenCalledTimes(1);
		expect(mockedAxios.get).toHaveBeenCalledWith('/config/mailer');
	});

	it('update mailer config', async (): Promise<void> => {
		expect.assertions(2);
		const config: MailerConfig ={
			'enabled': true,
			'host': 'smtp.example.com',
			'port': 465,
			'username': 'iqrf-gw',
			'password': 'password',
			'secure': MailerSmtpSecurity.TLS,
			'from': 'iqrf-gw@example.com',
			'theme': 'generic',
			'timeout': 20,
			'context': [],
			'clientHost': 'iqrf-gw.example.com',
			'persistent': false,
		};
		mockedAxios.put.mockResolvedValue({data: config});
		await service.editConfig(config);
		expect(mockedAxios.put).toHaveBeenCalledTimes(1);
		expect(mockedAxios.put).toHaveBeenCalledWith('/config/mailer', config);
	});

	it('update mailer config with IDN', async (): Promise<void> => {
		expect.assertions(2);
		const config: MailerConfig = {
			'enabled': true,
			'host': 'ondráček.eu',
			'port': 465,
			'username': 'iqrf-gw',
			'password': 'password',
			'secure': MailerSmtpSecurity.TLS,
			'from': 'iqrf-gw@ondráček.eu',
			'theme': 'generic',
			'timeout': 20,
			'context': [],
			'clientHost': null,
			'persistent': false,
		};
		const serializedConfig: MailerConfig = {
			'enabled': true,
			'host': 'xn--ondrek-sta66a.eu',
			'port': 465,
			'username': 'iqrf-gw',
			'password': 'password',
			'secure': MailerSmtpSecurity.TLS,
			'from': 'iqrf-gw@xn--ondrek-sta66a.eu',
			'theme': 'generic',
			'timeout': 20,
			'context': [],
			'clientHost': null,
			'persistent': false,
		};
		mockedAxios.put.mockResolvedValue({data: serializedConfig});
		await service.editConfig(config);
		expect(mockedAxios.put).toHaveBeenCalledTimes(1);
		expect(mockedAxios.put).toHaveBeenCalledWith('/config/mailer', serializedConfig);
	});

	it('test mailer config', async (): Promise<void> => {
		expect.assertions(2);
		const config: MailerConfig ={
			'enabled': true,
			'host': 'smtp.example.com',
			'port': 465,
			'username': 'iqrf-gw',
			'password': 'password',
			'secure': MailerSmtpSecurity.TLS,
			'from': 'iqrf-gw@example.com',
			'theme': 'generic',
			'timeout': 20,
			'context': [],
			'clientHost': 'iqrf-gw.example.com',
			'persistent': false,
		};
		mockedAxios.post.mockResolvedValue({data: config});
		await service.testConfig(config);
		expect(mockedAxios.post).toHaveBeenCalledTimes(1);
		expect(mockedAxios.post).toHaveBeenCalledWith('/config/mailer/test', config);
	});

});
