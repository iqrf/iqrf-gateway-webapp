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
 * Memory usage entity
 */
class MemoryUsageEntity extends BasicEntity {

	/**
	 * @var string Total memory size
	 */
	public $size;

	/**
	 * @var string Used memory size
	 */
	public $used;

	/**
	 * @var string Free memory size
	 */
	public $free;

	/**
	 * @var string Shared memory size
	 */
	public $shared;

	/**
	 * @var string Buffers memory size
	 */
	public $buffers;

	/**
	 * @var string Cache memory size
	 */
	public $cache;

	/**
	 * @var string Available memory size
	 */
	public $available;

	/**
	 * @var string Memory usage
	 */
	public $usage;

}
