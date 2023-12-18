/**
 * Copyright 2023 MICRORISC s.r.o.
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

import type {AxiosResponse} from 'axios';

import {BaseService} from '../BaseService';
import {
	type FileFormat,
	type FileType,
	type FileUploadResult,
	type UploaderFileData,
} from '../../types/Iqrf/Upgrade';

/**
 * IQRF Upgrade service
 */
export class UpgradeService extends BaseService {

	/**
	 * Upload file to filesystem and return upload path
	 * @param {File} file File to upload
	 * @param {FileType} type File type
	 * @return {Promise<string>} Path to uploaded file
	 */
	public uploadToFs(file: File, type: FileFormat): Promise<FileUploadResult> {
		const formData = new FormData();
		formData.append('format', type);
		formData.append('file', file);
		return this.axiosInstance.post('/iqrf/upload', formData)
			.then((response: AxiosResponse<FileUploadResult>): FileUploadResult => response.data);
	}

	/**
	 * Upload file from path to TR
	 * @param {string} path Path to uploaded file
	 * @param {FileType} type File type
	 */
	public uploadToTr(path: string, type: FileType): Promise<void> {
		const data: UploaderFileData = {
			name: path,
			type: type,
		};
		return this.axiosInstance.post('/iqrf/uploader', data).
			then(() => {return;});
	}
}
