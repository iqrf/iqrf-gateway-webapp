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
import {AxiosResponse} from 'axios';

/**
 * Creates a new file download element
 * @param {AxiosResponse} response Axios request response
 * @param {string} contentType Response content MIME
 * @param {string} fileName Name of downloaded file
 * @returns {HTMLAnchorElement} New file download element
 */
export function fileDownloader(response: AxiosResponse, contentType: string, fileName: string): HTMLAnchorElement {
	const contentDisposition = response.headers['content-disposition'];
	if (contentDisposition) {
		const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
		if (fileNameMatch !== null && fileNameMatch.length === 2) {
			fileName = fileNameMatch[1];
		}
	}
	let data = response.data;
	if (contentType === 'application/json') { // default processing is for binary files
		data = JSON.stringify(data);
	}
	const blob = new Blob([data], {type: contentType});
	const fileUrl = window.URL.createObjectURL(blob);
	const file = document.createElement('a');
	file.href = fileUrl;
	file.setAttribute('download', fileName);
	document.body.appendChild(file);
	return file;
}
