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

/**
 * Mailer theme enum
 */
export enum MailerTheme {

	/**
	 * Theme for generic IQRF gateways
	 */
	Generic = 'generic',

	/**
	 * Theme for IQAROS gateways
	 */
	IQAROS = 'iqaros',

}

/**
 * Mailer SMTP security enum
 */
export enum MailerSmtpSecurity {

	/**
	 * Plain-text
	 * @deprecated Use `null` instead
	 */
	PlainText = 'null',

	/**
	 * TLS
	 */
	TLS = 'ssl',

	/**
	 * STARTTLS
	 */
	STARTTLS = 'tls',

}

/**
 * Mailer configuration
 */
export interface MailerConfig {

	/**
	 * SMTP server enablement
	 */
	enabled: boolean;

	/**
	 * SMTP server host
	 */
	host: string;

	/**
	 * SMTP server port
	 */
	port: number;

	/**
	 * SMTP server username
	 */
	username: string;

	/**
	 * SMTP server password
	 */
	password: string;

	/**
	 * SMTP server security - `null` means plain-text connection
	 */
	secure: MailerSmtpSecurity|null;

	/**
	 * SMTP server sender email address
	 */
	from: string;

	/**
	 * Mailer theme
	 */
	theme: MailerTheme|string;

	/**
	 * SMTP server timeout
	 */
	timeout: number;

	/**
	 * Context for connecting to the SMTP server
	 */
	context: Array<any>;

	/**
	 * SMTP client hostname
	 */
	clientHost: string|null;

	/**
	 * Persistent connection to the SMTP server
	 */
	persistent: boolean;

}
