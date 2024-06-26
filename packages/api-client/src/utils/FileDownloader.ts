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

import { type AxiosResponse } from 'axios';

/**
 * File downloader
 */
export class FileDownloader {

	/**
	 * Downloads file from Axios response
	 * @param {AxiosResponse<object|string>} response Axios response
	 * @param {string} contentType MIME content type
	 * @param {string} fileName Name of downloaded file
	 */
	public static downloadFromAxiosResponse(response: AxiosResponse<object|string>, contentType: string, fileName: string): void {
		const element = this.getDownloadElementFromAxiosResponse(response, contentType, fileName);
		element.click();
	}

	/**
	 * Creates a new file download element from Axios response
	 * @param {AxiosResponse<object|string>} response Axios response
	 * @param {string} contentType MIME content type
	 * @param {string} fileName Name of downloaded file
	 */
	public static getDownloadElementFromAxiosResponse(response: AxiosResponse<object|string>, contentType: string, fileName: string): HTMLAnchorElement {
		const contentDisposition = response.headers['content-disposition'] as string;
		if (contentDisposition) {
			const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
			if (fileNameMatch !== null && fileNameMatch.length === 2) {
				fileName = fileNameMatch[1];
			}
		}
		return this.getDownloadElement(response.data, contentType, fileName);
	}

	/**
	 * Downloads file from data
	 * @param {object|string} data Data to download
	 * @param {string} contentType MIME content type
	 * @param {string} fileName Name of downloaded file
	 */
	public static downloadFromData(data: object|string, contentType: string, fileName: string): void {
		const element = this.getDownloadElementFromData(data, contentType, fileName);
		element.click();
	}

	/**
	 * Creates a new file download element from data
	 * @param {object|string} data Data to download
	 * @param {string} contentType MIME content type
	 * @param {string} fileName Name of downloaded file
	 */
	public static getDownloadElementFromData(data: object|string, contentType: string, fileName: string): HTMLAnchorElement {
		return this.getDownloadElement(data, contentType, fileName);
	}

	/**
	 * Creates a new file download element
	 * @param {string} rawData Axios request response
	 * @param {string} contentType Response content MIME
	 * @param {string} fileName Name of downloaded file
	 * @returns {HTMLAnchorElement} New file download element
	 */
	private static getDownloadElement(rawData: object|string, contentType: string, fileName: string): HTMLAnchorElement {
		const data = contentType === 'application/json' && typeof rawData === 'object' ? JSON.stringify(rawData, null, '\t') : (rawData as string);
		const blob: Blob = new Blob([data], { type: contentType });
		const fileUrl: string = window.URL.createObjectURL(blob);
		const element: HTMLAnchorElement = document.createElement('a');
		element.href = fileUrl;
		element.setAttribute('download', fileName);
		document.body.appendChild(element);
		return element;
	}

}
