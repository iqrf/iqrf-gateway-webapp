/**
 * Copyright 2023-2025 MICRORISC s.r.o.
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
	 * STARTTLS
	 */
	STARTTLS = 'tls',

	/**
	 * TLS
	 */
	TLS = 'ssl',

}

/**
 * Mailer configuration
 */
export interface MailerConfig {

	/**
	 * SMTP client hostname
	 */
	clientHost: string|null;

	/**
	 * Context for connecting to the SMTP server
	 */
	context?: unknown[];

	/**
	 * SMTP server enablement
	 */
	enabled: boolean;

	/**
	 * SMTP server sender email address
	 */
	from: string;

	/**
	 * SMTP server host
	 */
	host: string;

	/**
	 * SMTP server password
	 */
	password: string;

	/**
	 * Persistent connection to the SMTP server
	 */
	persistent?: boolean;

	/**
	 * SMTP server port
	 */
	port: number;

	/**
	 * SMTP server security - `null` means plain-text connection
	 */
	secure: MailerSmtpSecurity|null;

	/**
	 * Mailer theme
	 */
	theme: MailerTheme|string;

	/**
	 * SMTP server timeout
	 */
	timeout?: number;

	/**
	 * SMTP server username
	 */
	username: string;

}

/**
 * Mailer headers
 */
export interface MailerHeaders {

	/**
	 * Default SMTP configuration
	 */
	defaultConfig: boolean;
}

/**
 * Mailer
 */
export interface MailerGetConfigResponse {

	/**
	 * Mailer configuration
	 */
	config: MailerConfig;

	/**
	 * Mailer headers
	 */
	headers: MailerHeaders | null;
}
