/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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
 * Device backup data interface
 */
export interface IBackupData {
	/**
	 * Backup data
	 */
	data: string

	/**
	 * Device address
	 */
	deviceAddr: number

	/**
	 * DPA version
	 */
	dpaVer: number

	/**
	 * Module ID
	 */
	mid: number

	/**
	 * Is device online?
	 */
	online: boolean
}

/**
 * Device restore data interface
 */
export interface IRestoreData {
	/**
	 * Device address
	 */
	Address: string

	/**
	 * Coordinator data
	 */
	DataC?: string

	/**
	 * Node data
	 */
	DataN?: string

	/**
	 * Device type
	 */
	Device: string

	/**
	 * DPA version
	 */
	Version: string
}

/**
 * OTA upload configuration instance interface
 */
export interface IOtaUpload {
	/**
	 * Component name
	 */
	component: string

	/**
	 * Component instance name
	 */
	instance: string

	/**
	 * Upload path
	 */
	uploadPath: string

	/**
	 * Upload suffix
	 */
	uploadPathSuffix: string
}
