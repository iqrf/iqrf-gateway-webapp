<?php

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
declare(strict_types = 1);

namespace App\Models\Mail;

use Nette\IOException;
use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Nette\Utils\FileSystem;

/**
 * Mailer configuration manager
 */
class ConfigurationManager {

	/**
	 * @var string Path to the configuration file
	 */
	private $path;

	/**
	 * Returns the configuration schema
	 * @return Schema Configuration schema
	 */
	public function getConfigSchema(): Schema {
		return Expect::structure([
			'enabled' => Expect::bool(false),
			'host' => Expect::string('localhost')->dynamic(),
			'port' => Expect::int(25)->dynamic(),
			'username' => Expect::string('root')->dynamic(),
			'from' => Expect::string('iqrf-gw@localhost.localdomain')->dynamic(),
			'password' => Expect::string('')->dynamic(),
			'secure' => Expect::anyOf(null, 'ssl', 'tls')->default(null)->dynamic(),
			'timeout' => Expect::int()->dynamic(),
			'context' => Expect::arrayOf('array')->dynamic(),
			'clientHost' => Expect::string()->dynamic(),
			'persistent' => Expect::bool(false)->dynamic(),
			'theme' => Expect::string('generic')->dynamic(),
		])->castTo('array');
	}

	/**
	 * Constructor
	 * @param string $path Path to the configuration file
	 */
	public function __construct(string $path) {
		$this->path = $path;
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
		try {
			$content = FileSystem::read($this->path);
			$configuration = Neon::decode($content) ?? [];
		} catch (IOException | NeonException $e) {
			$configuration = [];
		}
		return (new Processor())->process($this->getConfigSchema(), $configuration);
	}

	/**
	 * Writes the mailer configuration
	 * @param array<string, bool|int|string> $configuration Mailer configuration to write
	 * @throws IOException
	 */
	public function write(array $configuration): void {
		$content = Neon::encode($configuration, Neon::BLOCK);
		FileSystem::write($this->path, $content);
	}

}
