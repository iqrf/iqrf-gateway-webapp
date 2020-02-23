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

namespace App\CloudModule\Models;

use App\CoreModule\Models\FileManager;
use App\ServiceModule\Enums\ServiceStates;
use App\ServiceModule\Models\SystemDManager;
use Nette\IOException;
use Nette\Utils\Strings;

/**
 * PIXLA management system manager
 */
class PixlaManager {

	/**
	 * @var FileManager File manager
	 */
	private $fileManager;

	/**
	 * @var SystemDManager SystemD service manager
	 */
	private $serviceManager;

	/**
	 * Constructor
	 * @param FileManager $fileManager File manager
	 * @param SystemDManager $serviceManager SystemD service manager
	 */
	public function __construct(FileManager $fileManager, SystemDManager $serviceManager) {
		$this->fileManager = $fileManager;
		$this->serviceManager = $serviceManager;
	}

	/**
	 * Disables and stops PIXLA client service
	 */
	public function disableService(): void {
		$this->serviceManager->disable();
	}

	/**
	 * Enables and starts PIXLA client service
	 */
	public function enableService(): void {
		$this->serviceManager->enable();
	}

	/**
	 * Returns PIXLA client service status
	 */
	public function getServiceStatus(): ServiceStates {
		return $this->serviceManager->isEnabled();
	}

	/**
	 * Returns PIXLA token
	 * @return string|null PIXLA token
	 */
	public function getToken(): ?string {
		try {
			return Strings::trim($this->fileManager->read('customer_id'), "\n");
		} catch (IOException $e) {
			return null;
		}
	}

}
