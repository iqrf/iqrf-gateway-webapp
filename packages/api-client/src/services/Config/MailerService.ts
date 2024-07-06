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

import { type AxiosResponse } from 'axios';
import * as punycode from 'punycode/';

import { MailerSmtpSecurity } from '../../types/Config';
import {
	type MailerConfig,
	type MailerGetConfigResponse,
} from '../../types/Config';
import { BaseService } from '../BaseService';

/**
 * Mailer configuration service
 */
export class MailerService extends BaseService {

	/**
	 * Fetches mailer configuration
	 * @return {Promise<MailerGetConfigResponse>} Mailer data
	 */
	public async getConfig(): Promise<MailerGetConfigResponse> {
		const response: AxiosResponse<MailerConfig> = await this.axiosInstance.get('/config/mailer');
		const defaultConfig = response.headers['x-smtp-default-config'] as string | null | undefined;
		return {
			headers: (defaultConfig !== undefined && defaultConfig !== null) ? { defaultConfig: defaultConfig === '1' } : null,
			config: this.deserializeConfig(response.data),
		};
	}

	/**
	 * Edits mailer configuration
	 * @param {MailerConfig} config Mailer configuration
	 */
	public async editConfig(config: MailerConfig): Promise<void> {
		await this.axiosInstance.put('/config/mailer', this.serializeConfig(config));
	}

	/**
	 * Tests mailer configuration
	 * @param {MailerConfig} config Mailer configuration
	 */
	public async testConfig(config: MailerConfig): Promise<void> {
		await this.axiosInstance.post('/config/mailer/test', this.serializeConfig(config));
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
