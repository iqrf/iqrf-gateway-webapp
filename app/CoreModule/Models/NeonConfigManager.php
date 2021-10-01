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

namespace App\CoreModule\Models;

use Nette\IOException;
use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon;
use Nette\Utils\FileSystem;

/**
 * Neon configuration file manager
 */
class NeonConfigManager {

	/**
	 * @var string $path Path to configuration file
	 */
	private $path;

	/**
	 * Constructor
	 * @param string $path Path to configuration file
	 */
	public function __construct(string $path) {
		$this->path = $path;
	}

	/**
	 * Reads neon configuration file and returns contents
	 * @return array<string, mixed> Configuration array
	 * @throws IOException
	 * @throws NeonException
	 */
	public function read(): array {
		$content = FileSystem::read($this->path);
		return Neon::decode($content);
	}

}
