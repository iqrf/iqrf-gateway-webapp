/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

/**
 * SMTP server service
 */
class MailerService {

	/**
	 * Retrieves SMTP configuration
	 * @return {Promise<ISmtp>} SMTP configuration
	 */
	getConfig(): Promise<ISmtp> {
		return axios.get('/config/mailer', {headers: authorizationHeader()})
			.then((response: AxiosResponse): ISmtp => {
				const config: ISmtp = response.data;
				config.from = punycode.toUnicode(config.from);
				return config;
			});
	}

	/**
	 * Saves SMTP configuration
	 * @param {ISmtp} config SMTP configuration
	 */
	saveConfig(config: ISmtp): Promise<AxiosResponse> {
		config.from = punycode.toASCII(config.from);
		return axios.put('/config/mailer', config, {headers: authorizationHeader()});
	}

	/**
	 * Attepts to send mail with current SMTP configuration
	 * @param {ISmtp} config SMTP configuration
	 */
	testConfig(config: ISmtp): Promise<AxiosResponse> {
		config.from = punycode.toASCII(config.from);
		return axios.post('/config/mailer/test', config, {headers: authorizationHeader()});
	}
}

export default new MailerService();
