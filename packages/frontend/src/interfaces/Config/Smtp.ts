/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

import {SmtpSecurity} from '@/enums/Config/Smtp';

/**
 * SMTP configuration interface
 */
export interface ISmtp {
	/**
	 * SMTP service enabled
	 */
	enabled: boolean

	/**
	 * Server name
	 */
	host: string

	/**
	 * Port number
	 */
	port: number

	/**
	 * Username
	 */
	username: string

	/**
	 * Password
	 */
	password: string

	/**
	 * Security protocol
	 */
	secure: SmtpSecurity|null

	/**
	 * Sender
	 */
	from: string

	/**
	 * Mail theme
	 */
	theme?: string

	/**
	 * Context for connecting to the SMTP server
	 */
	context?: Record<string, Record<string, any>>

	/**
	 * SMTP client hostname
	 */
	clientHost?: string|null

	/**
	 * Persistent connection to SMTP server
	 */
	persistent?: boolean
}
