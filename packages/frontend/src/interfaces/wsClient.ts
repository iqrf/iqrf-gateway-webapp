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

import DaemonMessage from '@/ws/DaemonMessage';

/**
 * Generic WebSocket client state interface
 */
interface GenericClientState {
	/**
	 * Is client connecting?
	 */
	isConnected: boolean;

	/**
	 * Is client reconnecting?
	 */
	reconnecting: boolean;

	/**
	 * Has client reconnected?
	 */
	hasReconnected: boolean;

	/**
	 * Number of received messages
	 */
	receivedMessages: number;
}

/**
 * Daemon WebSocket client state
 */
export interface DaemonClientState extends GenericClientState {
	/**
	 * Sent requests
	 */
	requests: Record<string, any>;

	/**
	 * Received responses
	 */
	responses: Record<string, any>;

	/**
	 * Message objects
	 */
	messages: Array<DaemonMessage>

	/**
	 * IQRF Gateway Daemon version
	 */
	version: string;

	/**
	 * Daemon API message ID for version request
	 */
	versionMsgId: string;
}

/**
 * Daemon Monitor WebSocket client state
 */
export interface MonitorClientState extends GenericClientState {
	/**
	 * Current daemon mode
	 */
	mode: string

	/**
	 * Modal window state
	 */
	modal: boolean;

	/**
	 * Daemon queue length
	 */
	queueLen: number|string;

	/**
	 * Network enumeration in progress
	 */
	enumInProgress: boolean;

	/**
	 * Sensor data collecting running
	 */
	dataReadingInProgress: boolean;
}
