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

namespace App\ApiModule\Version0\Entities\Request;

use Apitte\Core\Mapping\Request\BasicEntity;

/**
 * User create entity
 */
class UserCreateEntity extends BasicEntity {

	/**
	 * @var string User name
	 */
	public $username;

	/**
	 * @var string Password
	 */
	public $password;

	/**
	 * @var string User role
	 */
	public $role = 'normal';

	/**
	 * @var string Language
	 */
	public $language = 'en';

}
