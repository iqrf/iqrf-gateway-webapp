<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace App\NetworkModule\Enums;

/**
 * Network connections states enum
 */
enum ConnectionStates: string {

	/// There is a connection to the network
	case ACTIVATED = 'activated';
	/// A network connection is being prepared
	case ACTIVATING = 'activating';
	/// The network connection is being torn down and cleaned up
	case DEACTIVATING = 'deactivating';
	/// The network connection is disconnected and will be removed
	case DEACTIVATED = 'deactivated';
	/// The state of the connection is unknown
	case UNKNOWN = 'unknown';

}
