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

namespace App\InstallModule\Models;

use Nette\Utils\Strings;

/**
 * PHP module manager
 */
class PhpModuleManager {

	/**
	 * Required PHP extensions and their modules
	 */
	private const REQUIRED_EXTENSIONS_MODULES = [
		'curl' => 'curl',
		'dom' => 'xml',
		'fileinfo' => 'common',
		'iconv' => 'common',
		'json' => 'json',
		'mbstring' => 'mbstring',
		'openssl' => 'openssl',
		'PDO' => 'common',
		'pdo_sqlite' => 'sqlite3',
		'posix' => 'common',
		'SimpleXML' => 'xml',
		'sqlite3' => 'sqlite3',
		'xml' => 'xml',
		'xmlreader' => 'xml',
		'xmlwriter' => 'xml',
		'xsl' => 'xml',
		'zip' => 'zip',
	];

	/**
	 * Checks installed and loaded PHP modules
	 * @return array{allExtensionsLoaded: bool, missing?: array{extensions: array<string>, packages?: array<string>}} Missing extensions meta
	 */
	public static function checkModules(): array {
		$version = Strings::substring(phpversion(), 0, 3);
		$debianBased = file_exists('/etc/debian_version');
		$loaded = get_loaded_extensions();
		$extensions = [];
		$packages = [];

		foreach (self::REQUIRED_EXTENSIONS_MODULES as $extension => $package) {
			if (in_array($extension, $loaded, true)) {
				continue;
			}
			$extensions[] = $extension;
			if (!$debianBased) {
				continue;
			}
			$packageName = ($extension === 'openssl') ? $package : sprintf('php%s-%s', $version, $package);
			if (!in_array($packageName, $packages, true)) {
				$packages[] = $packageName;
			}
		}

		$extensionCheck = ['allExtensionsLoaded' => $extensions === []];
		if ($extensions !== []) {
			$extensionCheck['missing'] = ['extensions' => $extensions];
			if ($debianBased) {
				$extensionCheck['missing']['packages'] = $packages;
			}
		}
		return $extensionCheck;
	}

}
