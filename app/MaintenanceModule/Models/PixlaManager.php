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

namespace App\MaintenanceModule\Models;

use App\CoreModule\Models\FileManager;
use Nette\IOException;
use Nette\Utils\Strings;

/**
 * PIXLA management system manager
 */
class PixlaManager {

	/**
	 * @var FileManager File manager
	 */
	private FileManager $fileManager;

	/**
	 * @var string File containing PIXLA token
	 */
	private const FILE_NAME = 'customer_id';

	/**
	 * Constructor
	 * @param FileManager $fileManager File manager
	 */
	public function __construct(FileManager $fileManager) {
		$this->fileManager = $fileManager;
	}

	/**
	 * Returns PIXLA token
	 * @return string|null PIXLA token
	 */
	public function getToken(): ?string {
		try {
			return Strings::trim($this->fileManager->read(self::FILE_NAME), "\n");
		} catch (IOException $e) {
			return null;
		}
	}

	/**
	 * Sets new PIXLA token
	 * @param string $token PIXLA token
	 * @throws IOException IO error
	 */
	public function setToken(string $token): void {
		$content = Strings::trim($token);
		$this->fileManager->write(self::FILE_NAME, $content);
	}

}
