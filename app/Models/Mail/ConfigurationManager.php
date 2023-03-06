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

namespace App\Models\Mail;

use Nette\IOException;
use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Utils\FileSystem;

/**
 * Mailer configuration manager
 */
class ConfigurationManager {

	/**
	 * Returns the configuration schema
	 * @return Structure Configuration schema
	 */
	public function getConfigSchema(): Structure {
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
			'clientHost' => Expect::string()->dynamic(),
			'persistent' => Expect::bool(false)->dynamic(),
			'theme' => Expect::string('generic')->dynamic(),
		])->castTo('array');
	}

	/**
	 * Constructor
	 * @param string $path Path to the configuration file
	 * @param array<string, mixed>|null $config Configuration
	 */
	public function __construct(
		private readonly string $path,
		private readonly ?array $config = null,
	) {
	}

	/**
	 * Returns the sender e-mail address
	 * @return string Sender e-mail address
	 */
	public function getFrom(): string {
		return $this->read()['from'];
	}

	/**
	 * Returns the theme name
	 * @return string Theme name
	 */
	public function getTheme(): string {
		return $this->read()['theme'];
	}

	/**
	 * Reads mailer configuration
	 * @return array<string, bool|int|string> Mailer configuration
	 */
	public function read(): array {
		if ($this->config !== null) {
			$configuration = $this->config;
		} else {
			try {
				$content = FileSystem::read($this->path);
				$configuration = Neon::decode($content) ?? [];
			} catch (IOException | NeonException $e) {
				$configuration = [];
			}
		}
		return (new Processor())->process($this->getConfigSchema(), $configuration);
	}

	/**
	 * Writes the mailer configuration
	 * @param array<string, bool|int|string> $configuration Mailer configuration to write
	 * @throws IOException
	 */
	public function write(array $configuration): void {
		$content = Neon::encode($configuration, true);
		FileSystem::write($this->path, $content);
	}

}
