<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

/**
 * PHP module manager
 */
class PhpModuleManager {

	/**
	 * Required php modules
	 */
	private const REQUIRED_MODULES = [
		'curl',
		'dom',
		'fileinfo',
		'iconv',
		'json',
		'mbstring',
		'openssl',
		'PDO',
		'pdo_sqlite',
		'posix',
		'SimpleXML',
		'sqlite3',
		'xml',
		'xmlreader',
		'xmlwriter',
		'xsl',
		'zip',
	];

	/**
	 * Checks installed and loaded PHP modules
	 * @return array<string, bool|array<int, string>> Array of missing extensions
	 */
	public static function checkModules(): array {
		$loaded = get_loaded_extensions();
		$missing = [];
		foreach (self::REQUIRED_MODULES as $module) {
			if (!in_array($module, $loaded, true)) {
				$missing[] = $module;
			}
		}
		$modules = ['allExtensionsLoaded' => $missing === []];
		if ($missing !== []) {
			$modules['missing'] = $missing;
		}
		return $modules;
	}

}
