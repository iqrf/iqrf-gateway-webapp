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

namespace App\MaintenanceModule\Enums;

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;

/**
 * Mender client actions
 * @method static MenderClientActions COMMIT()
 * @method static MenderClientActions INSTALL()
 * @method static MenderClientActions ROLLBACK()
 */
final class MenderClientActions extends Enum {

	use AutoInstances;

	/// Commit installed Mender artifact
	private const COMMIT = 'commit';
	/// Install Mender artifact
	private const INSTALL = 'install';
	/// Roll installed Mender artifact back
	private const ROLLBACK = 'rollback';

}
