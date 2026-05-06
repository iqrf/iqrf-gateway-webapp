<?php

/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

use App\InstallModule\Exceptions\FactoryPasswordNotConfiguredException;
use Iqrf\FileManager\IFileManager;
use Nette\IOException;

/**
 * Install wizard manager
 */
class InstallWizardManager {

	/**
	 * File containing hash of password for access to installation wizard
	 */
	public const PASSWORD_FILE = 'factory_password';

	/**
	 * Constructor
	 * @param IFileManager $fileManager Privileged file manager
	 */
	public function __construct(
		private readonly IFileManager $fileManager,
	) {
	}

	/**
	 * Checks if client can acecss installation wizard by comparing provided
	 * access password against stored hash of default password
	 */
	public function verifyAccess(string $password): bool {
		$hashed = $this->read();
		return password_verify($password, $hashed);
	}

	/**
	 * Read contents of factory password file, and return the contents stripped of whitespaces
	 * @return string Password file contents
	 * @throws FactoryPasswordNotConfiguredException Thrown if password file does not exist or is empty
	 * @throws IOException Thrown if file could not be read
	 */
	private function read(): string {
		if (!$this->fileManager->exists(self::PASSWORD_FILE)) {
			throw new FactoryPasswordNotConfiguredException('Factory password not configured.');
		}
		$contents = trim($this->fileManager->read(self::PASSWORD_FILE));
		if ($contents === '') {
			throw new FactoryPasswordNotConfiguredException('Factory password not configured.');
		}
		return $contents;
	}

}
