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

import type {AxiosResponse} from 'axios';
import * as punycode from 'punycode/';

import {BaseService} from '../BaseService';
import type {MailerConfig} from '../../types';
import {MailerSmtpSecurity} from '../../types';

/**
 * Mailer configuration service
 */
export class MailerService extends BaseService {

	/**
	 * Fetches mailer configuration
	 * @return {Promise<MailerConfig>} Mailer configuration
	 */
	public getConfig(): Promise<MailerConfig> {
		return this.axiosInstance.get('/config/mailer')
			.then((response: AxiosResponse<MailerConfig>): MailerConfig => this.deserializeConfig(response.data));
	}

	/**
	 * Edits mailer configuration
	 * @param {MailerConfig} config Mailer configuration
	 */
	public editConfig(config: MailerConfig): Promise<void> {
		return this.axiosInstance.put('/config/mailer', this.serializeConfig(config))
			.then((): void => {return;});
	}

	/**
	 * Tests mailer configuration
	 * @param {MailerConfig} config Mailer configuration
	 */
	public testConfig(config: MailerConfig): Promise<void> {
		return this.axiosInstance.post('/config/mailer/test', this.serializeConfig(config))
			.then((): void => {return;});
	}

	/**
	 * Deserializes mailer configuration
	 * @param {MailerConfig} config Mailer configuration to deserialize
	 * @return {MailerConfig} Mailer configuration
	 * @private
	 */
	private deserializeConfig(config: MailerConfig): MailerConfig {
		config.host = punycode.toUnicode(config.host);
		config.from = punycode.toUnicode(config.from);
		if (config.secure === MailerSmtpSecurity.PlainText) {
			config.secure = null;
		}
		return config;
	}

	/**
	 * Serializes mailer configuration
	 * @param {MailerConfig} config Mailer configuration to serialize
	 * @return {MailerConfig} Mailer configuration
	 * @private
	 */
	private serializeConfig(config: MailerConfig): MailerConfig {
		config.host = punycode.toASCII(config.host);
		config.from = punycode.toASCII(config.from);
		if (config.secure === MailerSmtpSecurity.PlainText) {
			config.secure = null;
		}
		return config;
	}

}
