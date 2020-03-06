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
 * IQRF IDE Macro
 */
class MacroEntity extends BasicEntity {

	/**
	 * @var string Macro name
	 */
	public $name;

	/**
	 * @var string Macro's request packet
	 */
	public $request;

	/**
	 * @var string|null Macro's note
	 */
	public $note;

	/**
	 * @var bool Is macro enabled?
	 */
	public $enabled;

	/**
	 * @var bool Macro's confirmation request
	 */
	public $confirmation;

}
