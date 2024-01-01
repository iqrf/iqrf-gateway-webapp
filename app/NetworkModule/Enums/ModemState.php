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

namespace App\NetworkModule\Enums;

/**
 * GSM modem modes
 */
enum ModemState: string {

	/// The modem is unusable
	case FAILED = 'failed';
	/// State unknown or not reportable
	case UNKNOWN = 'unknown';
	/// The modem is currently being initialized
	case INITIALIZING = 'initializing';
	/// The modem needs to be unlocked
	case LOCKED = 'locked';
	/// The modem is not enabled and is powered down
	case DISABLED = 'disabled';
	/// The modem is currently transitioning to the DISABLED state
	case DISABLING = 'disabling';
	/// The modem is currently transitioning to the ENABLED state
	case ENABLING = 'enabling';
	/// The modem is enabled and powered on but not registered with a network provider and not available for data connections
	case ENABLED = 'enabled';
	/// The modem is searching for a network provider to register with
	case SEARCHING = 'searching';
	/// The modem is registered with a network provider, and data connections and messaging may be available for use
	case REGISTERED = 'registered';
	/// The modem is disconnecting and deactivating the last active packet data bearer. This state will not be entered if more than one packet data bearer is active and one of the active bearers is deactivated.
	case DISCONNECTING = 'disconnecting';
	/// The modem is activating and connecting the first packet data bearer. Subsequent bearer activations when another bearer is already active do not cause this state to be entered
	case CONNECTING = 'connecting';
	/// One or more packet data bearers is active and connected
	case CONNECTED = 'connected';

}
