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
import {describe, expect, it} from 'vitest';

import {mockedAxios, mockedClient} from '../../mocks/axios';

import {MailerService} from '../../../src/services/Config';
import {type MailerConfig, MailerSmtpSecurity} from '../../../src/types/Config';

describe('MailerService', (): void => {

	/**
	 * @var {MailerService} service Mailer configuration service
	 */
	const service: MailerService = new MailerService(mockedClient);

	it('fetch mailer config', async (): Promise<void> => {
		expect.assertions(1);
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
		mockedAxios.onGet('/config/mailer')
			.reply(200, config);
		await service.getConfig()
			.then((actual: MailerConfig): void => {
				expect(actual).toStrictEqual(config);
			});
	});

	it('fetch mailer config with IDN', async (): Promise<void> => {
		expect.assertions(1);
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
		mockedAxios.onGet('/config/mailer')
			.reply(200, config);
		await service.getConfig()
			.then((actual: MailerConfig): void => {
				expect(actual).toStrictEqual(expected);
			});
	});

	it('update mailer config', async (): Promise<void> => {
		expect.assertions(0);
		const config: MailerConfig = {
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
		mockedAxios.onPut('/config/mailer', config)
			.reply(200, config);
		await service.editConfig(config);
	});

	it('update mailer config with IDN', async (): Promise<void> => {
		expect.assertions(0);
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
		mockedAxios.onPut('/config/mailer', serializedConfig)
			.reply(200, serializedConfig);
		await service.editConfig(config);
	});

	it('test mailer config', async (): Promise<void> => {
		expect.assertions(0);
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
		mockedAxios.onPost('/config/mailer/test', config)
			.reply(200, config);
		await service.testConfig(config);
	});

});
