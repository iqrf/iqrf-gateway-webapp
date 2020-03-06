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

namespace App\ApiModule\Version0\Entities\Response;

use Apitte\Core\Mapping\Response\BasicEntity;

/**
 * Gateway info entity
 */
class GatewayInfoEntity extends BasicEntity {

	/**
	 * @var string Board
	 */
	public $board;

	/**
	 * @var string|null Gateway ID
	 */
	public $gwId;

	/**
	 * @var string|null PIXLA token
	 */
	public $pixla;

	/**
	 * @var GatewayVersionsEntity IQRF Gateway software versions
	 */
	public $versions;

	/**
	 * @var string Gateway hostname
	 */
	public $hostname;

	/**
	 * @var InterfaceEntity[] Interfaces
	 */
	public $interfaces;

	/**
	 * @var DiskUsageEntity[] Disk usages
	 */
	public $diskUsages;

	/**
	 * @var MemoryUsageEntity Memory usage
	 */
	public $memoryUsage;

	/**
	 * @var SwapUsageEntity Swap usage
	 */
	public $swapUsage;

	/**
	 * @var object|null Coordinator info
	 */
	public $coordinator;

}
