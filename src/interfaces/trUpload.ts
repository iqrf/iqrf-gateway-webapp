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
 * IQRF OS upgrade metadata interface
 */
export interface IqrfOsUpgrade {
	/**
	 * IQRF OS build
	 */
	os: IIqrfOsUpgradeOs

	/**
	 * IQRF OS version
	 */
	osVersion: string

	/**
	 * DPA pretty version
	 */
	dpa: IIqrfOsUpgradeDpa

	/**
	 * IQRF OS notes
	 */
	notes: string
}

/**
 * IQRF OS upgrade OS metadata interface
 */

export interface IIqrfOsUpgradeOs {
	/**
	 * OS build
	 */
	build: string

	/**
	 * OS version
	 */
	version: string

	/**
	 * OS attributes
	 */
	attributes: IIqrfOsUpgradeAttribes

	/**
	 * Download path
	 */
	downloadPath: string
}

/**
 * IQRF OS upgrade DPA metadata interface
 */
export interface IIqrfOsUpgradeDpa {
	/**
	 * DPA version
	 */
	version: string

	/**
	 * DPA attributes
	 */
	attributes: IIqrfOsUpgradeAttribes

	/**
	 * Download path
	 */
	downloadPath: string
}

/**
 * IQRF OS upgrade  attributes interface
 */
export interface IIqrfOsUpgradeAttribes {
	/**
	 * Indicates beta version
	 */
	beta: boolean

	/**
	 * Indicates obsolete version
	 */
	obsolete: boolean
}

/**
 * IQRF OS upgrade files interface
 */
export interface IqrfOsUpgradeFiles {
	/**
	 * DPA file name
	 */
	dpa: string

	/**
	 * Array of IQRF OS patch file names
	 */
	os: Array<string>
}

/**
 * IQRF OS upgrade file interface
 */
export interface UploadUtilFile {
	/**
	 * File name
	 */
	name: string
	
	/**
	 * File type
	 */
	type: string
}

/**
 * File upload response interface
 */
export interface FileUpload {
	/**
	 * File name
	 */
	fileName: string

	/**
	 * File format
	 */
	format: string
}
