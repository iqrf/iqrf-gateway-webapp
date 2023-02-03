/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
import {authorizationHeader} from '@/helpers/authorizationHeader';
import axios, {AxiosResponse} from 'axios';
import {ISmtp} from '@/interfaces/Config/Smtp';
import punycode from 'punycode';
import {SmtpSecurity} from '@/enums/Config/Smtp';

/**
 * SMTP server service
 */
class MailerService {

	/**
	 * Deserialize SMTP configuration
	 * @param {ISmtp} config SMTP configuration to deserialize
	 * @return {ISmtp} Deserialized SMTP configuration
	 */
	private deserializeConfig(config: ISmtp): ISmtp {
		config.host = punycode.toUnicode(config.host);
		config.from = punycode.toUnicode(config.from);
		if (config.secure === null) {
			config.secure = SmtpSecurity.PLAINTEXT;
		}
		return config;
	}

	/***
	 * Serializes SMTP configuration
	 * @param {ISmtp} config SMTP configuration to serialize
	 * @return {ISmtp} Serialized SMTP configuration
	 */
	private serializeConfig(config: ISmtp): ISmtp {
		config.host = punycode.toASCII(config.host);
		config.from = punycode.toASCII(config.from);
		if (config.secure === SmtpSecurity.PLAINTEXT) {
			config.secure = null;
		}
		return config;
	}

	/**
	 * Retrieves SMTP configuration
	 * @return {Promise<ISmtp>} SMTP configuration
	 */
	getConfig(): Promise<ISmtp> {
		return axios.get('/config/mailer', {headers: authorizationHeader()})
			.then((response: AxiosResponse): ISmtp => {
				const config: ISmtp = response.data;
				return this.deserializeConfig(config);
			});
	}

	/**
	 * Saves SMTP configuration
	 * @param {ISmtp} config SMTP configuration
	 */
	saveConfig(config: ISmtp): Promise<AxiosResponse> {
		return axios.put('/config/mailer', this.serializeConfig(config), {headers: authorizationHeader()});
	}

	/**
	 * Attepts to send mail with current SMTP configuration
	 * @param {ISmtp} config SMTP configuration
	 */
	testConfig(config: ISmtp): Promise<AxiosResponse> {
		return axios.post('/config/mailer/test', this.serializeConfig(config), {headers: authorizationHeader()});
	}
}

export default new MailerService();
