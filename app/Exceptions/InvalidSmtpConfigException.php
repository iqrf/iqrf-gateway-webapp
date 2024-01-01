<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
declare(strict_types = 1);

namespace App\Exceptions;

use RuntimeException;

/**
 * The exception that indicates an invalid SMTP configuration
 */
class InvalidSmtpConfigException extends RuntimeException {

	/**
	 * Connection failed error code
	 */
	public const CONNECTION_FAILED = 1;

	/**
	 * Hello failed error code
	 */
	public const HELLO_FAILED = 2;

	/**
	 * STARTTLS not supported error code
	 */
	public const STARTTLS_NOT_SUPPORTED = 3;

	/**
	 * STARTTLS failed error code
	 */
	public const STARTTLS_FAILED = 4;

	/**
	 * Authentication unsupported error code
	 */
	public const AUTH_NOT_SUPPORTED = 5;

	/**
	 * Authentication failed error code
	 */
	public const AUTH_FAILED = 6;

	/**
	 * SSL certificate expired error code
	 */
	public const SSL_CERTIFICATE_EXPIRED = 7;

	/**
	 * SSL certificate not valid error code
	 */
	public const SSL_CERTIFICATE_INVALID = 8;

	/**
	 * SSL certificate not found error code
	 */
	public const SSL_CERTIFICATE_NOT_FOUND = 9;

}
