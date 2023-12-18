/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
 * IQRF Upload file format enum
 */
export enum FileFormat {
	/// Custom DPA handler
	HEX = 'hex',
	/// IQRF Plugin
	IQRF = 'iqrf',
	/// TR configuration
	TRCNFG = 'trcnfg'
}

export enum FileType {
	DPA = 'DPA',
	HEX = 'HEX',
	OS = 'OS',
}

/**
 * IQRF Upload result
 */
export interface FileUploadResult {
	/// Path to uploaded file
	fileName: string;
	/// File type
	format: FileType;
}

/**
 * IQRF Uploader file data
 */
export interface UploaderFileData {
	/// Path to file
	name: string;
	/// File type
	type: FileType;
}

/**
 * IQRF OS upgrades fetch metadata
 */
export interface OsUpgradeMetadata {
	/// Current OS build
	build: string;
	/// MCU type
	mcuType: number;
	/// Current OS version
	version: string;
}
