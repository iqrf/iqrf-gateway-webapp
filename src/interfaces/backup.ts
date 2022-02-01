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

/**
 * Gateway backup interface
 */
export interface IGwBackup {
	/**
	 * Software configuration
	 */
	software: IGwBackupSoftware

	/**
	 * System configuration
	 */
	system: IGwBackupSystem
}

/**
 * Gateway software backup interface
 */
export interface IGwBackupSoftware {
	/**
	 * IQRF Software
	 */
	iqrf: boolean

	/**
	 * Mender
	 */
	mender: boolean

	/**
	 * M/Monit
	 */
	monit: boolean

	/**
	 * Pixla
	 */
	pixla: boolean
}

/**
 * Gateway system backup interface
 */
export interface IGwBackupSystem {
	/**
	 * Hostname and hosts
	 */
	hostname: boolean

	/**
	 * Network manager
	 */
	network: boolean

	/**
	 * Timezone
	 */
	time: boolean

	/**
	 * NTP/Timesync
	 */
	ntp: boolean

	/**
	 * Systemd journal
	 */
	journal: boolean
}
