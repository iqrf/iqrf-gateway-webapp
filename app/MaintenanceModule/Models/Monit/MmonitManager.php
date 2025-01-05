<?php

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
declare(strict_types = 1);

namespace App\MaintenanceModule\Models\Monit;

use App\MaintenanceModule\Exceptions\MonitConfigErrorException;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;
use Nette\Schema\Processor;

/**
 * M/Monit configuration manager
 */
class MmonitManager extends BaseMonitManager {

	/**
	 * M/Monit configuration file
	 */
	private const FILE_NAME = 'mmonit';

	/**
	 * Server and credentials pattern
	 */
	private const PATTERN = '/^set\smmonit\s(?\'url\'.+)\/collector$/';

	/**
	 * Returns M/Monit configuration file schema
	 * @return Structure M/Monit server schema
	 */
	public function getSchema(): Structure {
		return Expect::structure([
			'enabled' => Expect::bool()->required(),
			'credentials' => Expect::structure([
				'username' => Expect::string()->required(),
				'password' => Expect::string()->required(),
			])->castTo('array'),
			'server' => Expect::string()->default('https://mmonit.example.corp')->required(),
		])->castTo('array');
	}

	/**
	 * Reads M/Monit configuration
	 * @return array{enabled: bool, credentials: array{username: string, password: string}, server: string} M/Monit configuration
	 * @throws MonitConfigErrorException Failed to read M/Monit configuration
	 */
	public function readConfig(): array {
		$this->migrateConfig();
		$data = $this->readConfigLine(self::CONFIG_AVAILABLE . '/' . self::FILE_NAME, self::PATTERN);
		return $this->decodeConfig($data);
	}

	/**
	 * Writes M/Monit configuration
	 * @param array{enabled: bool, credentials: array{username: string, password: string}, server: string} $newConfig New M/Monit configuration
	 * @throws MonitConfigErrorException Monit configuration file contains invalid content
	 */
	public function writeConfig(array $newConfig): void {
		$line = $this->encodeConfig($newConfig);
		$this->writeConfigLine(self::CONFIG_AVAILABLE . '/' . self::FILE_NAME, self::PATTERN, $line);
		$this->enableConfigFile(self::FILE_NAME, $newConfig['enabled']);
	}

	/**
	 * Encodes M/Monit configuration
	 * @param array{enabled: bool, credentials: array{username: string, password: string}, server: string} $config M/Monit configuration
	 * @return string Encoded M/Monit configuration
	 * @throws MonitConfigErrorException
	 */
	private function encodeConfig(array $config): string {
		$url = parse_url($config['server']);
		if ($url === false) {
			throw new MonitConfigErrorException('M/Monit server URL is invalid.');
		}
		$server = $url['host'];
		if (isset($url['port'])) {
			$server .= ':' . $url['port'];
		}
		if (isset($url['path'])) {
			$server .= rtrim($url['path'], '/');
		}
		$username = urlencode($config['credentials']['username']);
		$password = urlencode($config['credentials']['password']);
		return sprintf('set mmonit %s://%s:%s@%s/collector', $url['scheme'], $username, $password, $server);
	}

	/**
	 * Decodes M/Monit configuration
	 * @param array<string> $data M/Monit configuration to decode
	 * @return array{enabled: bool, credentials: array{username: string, password: string}, server: string} Decoded M/Monit configuration
	 * @throws MonitConfigErrorException
	 */
	private function decodeConfig(array $data): array {
		$url = parse_url($data['url']);
		if ($url === false || !in_array($url['scheme'], ['http', 'https'], true)) {
			throw new MonitConfigErrorException('M/Monit server URL is invalid.');
		}
		$server = $url['scheme'] . '://' . $url['host'];
		if (isset($url['port'])) {
			$server .= ':' . $url['port'];
		}
		if (isset($url['path'])) {
			$server .= $url['path'];
		}
		$config = [
			'enabled' => $this->isConfigFileEnabled(self::FILE_NAME),
			'credentials' => [
				'username' => urldecode($url['user']),
				'password' => urldecode($url['pass']),
			],
			'server' => $server,
		];
		$processor = new Processor();
		return $processor->process($this->getSchema(), $config);
	}

	/**
	 * Migrate M/Monit server configuration
	 * @throws MonitConfigErrorException Failed to migrate M/Monit configuration
	 */
	private function migrateConfig(): void {
		if ($this->isConfigFileAvailable('mmonit')) {
			return;
		}
		try {
			$data = $this->readConfigLine('monitrc', self::PATTERN);
			$config = $this->decodeConfig($data);
			$this->fileManager->write(self::CONFIG_AVAILABLE . '/' . self::FILE_NAME, $this->encodeConfig($config));
		} catch (MonitConfigErrorException $e) {
			throw new MonitConfigErrorException('Failed to migrate M/Monit configuration.', $e->getCode(), $e);
		}
	}

}
