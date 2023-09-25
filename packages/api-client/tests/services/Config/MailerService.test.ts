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
import {beforeEach, describe, expect, it} from 'vitest';

import {mockedAxios, mockedClient} from '../../mocks/axios';

import {MailerService} from '../../../src/services/Config';
import {type MailerConfig, MailerSmtpSecurity} from '../../../src/types/Config';

describe('MailerService', (): void => {

	beforeEach((): void => {
		mockedAxios.reset();
	});

	/**
	 * Default mailer configuration
	 */
	const defaultConfig: MailerConfig = {
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

	/**
	 * Base of custom mailer configuration
	 */
	const baseConfig: MailerConfig = {
		...defaultConfig,
		'enabled': true,
		'host': 'smtp.example.com',
		'port': 465,
		'username': 'iqrf-gw',
		'password': 'password',
		'secure': MailerSmtpSecurity.TLS,
		'from': 'iqrf-gw@example.com',
	};

	/**
	 * @var {MailerService} service Mailer configuration service
	 */
	const service: MailerService = new MailerService(mockedClient);

	it('fetch mailer config', async (): Promise<void> => {
		expect.assertions(1);
		mockedAxios.onGet('/config/mailer')
			.reply(200, defaultConfig);
		await service.getConfig()
			.then((actual: MailerConfig): void => {
				expect(actual).toStrictEqual(defaultConfig);
			});
	});

	it('fetch mailer config with IDN', async (): Promise<void> => {
		expect.assertions(1);
		const config: MailerConfig = {
			...baseConfig,
			'host': 'xn--ondrek-sta66a.eu',
			'from': 'iqrf-gw@xn--ondrek-sta66a.eu',
		};
		const expected: MailerConfig = {
			...baseConfig,
			'host': 'ondráček.eu',
			'from': 'iqrf-gw@ondráček.eu',
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
			...baseConfig,
			'clientHost': 'iqrf-gw.example.com',
		};
		mockedAxios.onPut('/config/mailer', config)
			.reply(200, config);
		await service.editConfig(config);
	});

	it('update mailer config - plain text transport', async (): Promise<void> => {
		expect.assertions(3);
		const config: MailerConfig = {
			...baseConfig,
			'port': 25,
			'secure': MailerSmtpSecurity.PlainText,
			'clientHost': 'iqrf-gw.example.com',
		};
		const expected: MailerConfig = {
			...config,
			secure: null,
		};
		mockedAxios.onPut('/config/mailer', expected)
			.reply(200, config);
		await service.editConfig(config);
		expect(mockedAxios.history.put).toBeDefined();
		expect(mockedAxios.history.put.length).toStrictEqual(1);
		expect(JSON.parse(mockedAxios.history.put[0].data)).toStrictEqual(expected);
	});

	it('update mailer config with IDN', async (): Promise<void> => {
		expect.assertions(0);
		const config: MailerConfig = {
			...baseConfig,
			'host': 'ondráček.eu',
			'from': 'iqrf-gw@ondráček.eu',
		};
		const serializedConfig: MailerConfig = {
			...baseConfig,
			'host': 'xn--ondrek-sta66a.eu',
			'from': 'iqrf-gw@xn--ondrek-sta66a.eu',
		};
		mockedAxios.onPut('/config/mailer', serializedConfig)
			.reply(200, serializedConfig);
		await service.editConfig(config);
	});

	it('test mailer config', async (): Promise<void> => {
		expect.assertions(0);
		const config: MailerConfig = {
			...baseConfig,
			'clientHost': 'iqrf-gw.example.com',
		};
		mockedAxios.onPost('/config/mailer/test', config)
			.reply(200, config);
		await service.testConfig(config);
	});

});
