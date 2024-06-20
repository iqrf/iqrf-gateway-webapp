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

import { type AxiosResponse } from 'axios';

import {
	type FileFormat,
	type FileType,
	type FileUploadResult,
	type UploaderFileData,
} from '../../types/Iqrf';
import { BaseService } from '../BaseService';

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
	public async uploadToFs(file: File, type: FileFormat): Promise<FileUploadResult> {
		const formData: FormData = new FormData();
		formData.append('format', type);
		formData.append('file', file);
		const response: AxiosResponse<FileUploadResult> =
			await this.axiosInstance.post('/iqrf/upload', formData);
		return response.data;
	}

	/**
	 * Upload file from path to TR
	 * @param {string} path Path to uploaded file
	 * @param {FileType} type File type
	 */
	public async uploadToTr(path: string, type: FileType): Promise<void> {
		const data: UploaderFileData = {
			name: path,
			type: type,
		};
		await this.axiosInstance.post('/iqrf/uploader', data);
	}
}
