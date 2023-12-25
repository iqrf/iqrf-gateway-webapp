<?php

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
declare(strict_types = 1);

namespace App\Entities;

use JsonSerializable;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use function gethostname;

class MailerConfiguration implements JsonSerializable {

	/**
	 * Constructor
	 * @param bool $enabled Is mailer enabled?
	 * @param string $host SMTP server address
	 * @param int $port SMTP server port
	 * @param string $username SMTP server username
	 * @param string $password SMTP server password
	 * @param string $from Sender's e-mail address
	 * @param string|null $secure SMTP server security type
	 * @param int $timeout SMTP server connection timeout
	 * @param array<mixed>|null $context SMTP server context
	 * @param string|null $clientHost Client hostname
	 * @param bool $persistent Persistent connection to SMTP server?
	 * @param string $theme Mail theme
	 */
	public function __construct(
		public bool $enabled = false,
		public string $host = 'localhost',
		public int $port = 25,
		public string $username = 'root',
		public string $password = '',
		public string $from = 'iqrf-gw@localhost.localdomain',
		public ?string $secure = null,
		public int $timeout = 20,
		public ?array $context = null,
		public ?string $clientHost = null,
		public bool $persistent = false,
		public string $theme = 'generic',
	) {
	}

	/**
	 * Deserializes mailer configuration from JSON
	 * @param array{enabled: bool, host: string, port: int, username: string, password: string, from: string, secure: ?string, timeout: int, context: ?array<mixed>, clientHost: ?string, persistent: bool, theme: string} $json JSON serialized mailer configuration
	 * @return self Mailer configuration
	 */
	public static function jsonDeserialize(array $json): self {
		return new self(
			$json['enabled'],
			$json['host'],
			$json['port'],
			$json['username'],
			$json['password'],
			$json['from'],
			$json['secure'],
			$json['timeout'],
			$json['context'],
			($json['clientHost'] !== null && $json['clientHost'] !== '') ? $json['clientHost'] : gethostname(),
			$json['persistent'],
			$json['theme'],
		);
	}

	/**
	 * Merges the mailer configuration with the default configuration
	 * @param array{enabled: bool, host: string, port: int, username: string, password: string, from: string, secure: ?string, timeout: int, context: ?array<mixed>, clientHost: ?string, persistent: bool, theme: string}|MailerConfiguration $configuration Mailer configuration
	 * @return self Merged mailer configuration
	 */
	public static function mergeDefaults(array|self $configuration): self {
		return (new Processor())->process(self::getValidationSchema(), $configuration);
	}

	/**
	 * Returns the mailer configuration schema
	 * @return Structure Mailer configuration schema
	 */
	public static function getValidationSchema(): Structure {
		return Expect::structure([
			'enabled' => Expect::bool(false),
			'host' => Expect::string('localhost')->dynamic(),
			'port' => Expect::int(25)->dynamic(),
			'username' => Expect::string('root')->dynamic(),
			'from' => Expect::string('iqrf-gw@localhost.localdomain')->dynamic(),
			'password' => Expect::string('')->dynamic(),
			'secure' => Expect::anyOf(null, 'ssl', 'tls')->default(null)->dynamic(),
			'timeout' => Expect::int(20)->dynamic(),
			'context' => Expect::arrayOf('array')->dynamic(),
			'clientHost' => Expect::anyOf(null, Expect::string())->dynamic(),
			'persistent' => Expect::bool(false)->dynamic(),
			'theme' => Expect::string('generic')->dynamic(),
		])->castTo('array')->transform(self::jsonDeserialize(...));
	}

	/**
	 * Checks if the credentials are set
	 * @return bool Are credentials set?
	 */
	public function hasCredentials(): bool {
		return $this->username !== '' && $this->password !== '';
	}

	/**
	 * Serializes mailer configuration into JSON
	 * @return array{enabled: bool, host: string, port: int, username: string, password: string, from: string, secure: ?string, timeout: int, context: ?array<mixed>, clientHost: ?string, persistent: bool, theme: string} JSON serialized mailer configuration
	 */
	public function jsonSerialize(): array {
		return [
			'enabled' => $this->enabled,
			'host' => $this->host,
			'port' => $this->port,
			'username' => $this->username,
			'from' => $this->from,
			'password' => $this->password,
			'secure' => $this->secure,
			'timeout' => $this->timeout,
			'context' => $this->context,
			'clientHost' => $this->clientHost,
			'persistent' => $this->persistent,
			'theme' => $this->theme,
		];
	}

}
