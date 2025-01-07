/**
 * Copyright 2023-2025 MICRORISC s.r.o.
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
 * Error response
 */
export interface ErrorResponse {

	/**
	 * Error code
	 */
	code: number,

	/**
	 * Error message
	 */
	message: string,

	/**
	 * Response status
	 */
	status: 'error'

}

/**
 * Email sent response
 */
export interface EmailSentResponse {

	/**
	 * Has been the email sent?
	 */
	emailSent: boolean;

}

export type FileResponseType = Blob | object | string | unknown[];

/**
 * File response
 * @template {FileResponseType} T File content type
 */
export class FileResponse<T extends FileResponseType> {

	/**
	 * @property {T} content File content
	 */
	public content: T;

	/**
	 * @property {string} contentType Content MIME type
	 */
	public contentType: string;

	/**
	 * @property {string} name File name
	 */
	public name: string;

	/**
	 * File constructor
	 * @param {T} content File content
	 * @param {string} contentType Content MIME type
	 * @param {string} name File name
	 */
	public constructor(content: T, contentType: string, name: string) {
		this.content = content;
		this.contentType = contentType;
		this.name = name;
	}

	/**
	 * Creates a new file from Axios response
	 * @template {FileResponseType} T File content type
	 * @param {AxiosResponse<T>} response Axios response
	 * @param {string} name File name
	 * @return {File<T>} New file entity
	 */
	public static fromAxiosResponse<T extends FileResponseType>(response: AxiosResponse<T>, name: string | null = null): FileResponse<T> {
		const contentDisposition: string | undefined = response.headers['content-disposition'] as string | undefined;
		const contentType: string = (response.headers['content-type'] as string | undefined) ?? 'application/octet-stream';
		let fileName: string | null = name;
		if (fileName === null && contentDisposition) {
			const fileNameMatch: RegExpMatchArray | null = /filename="(.+)"/.exec(contentDisposition);
			if (fileNameMatch !== null && fileNameMatch.length === 2) {
				fileName = fileNameMatch[1];
			}
		}
		if (fileName === null) {
			throw new Error('File name is not specified');
		}
		return new FileResponse(response.data, contentType, fileName);
	}

	/**
	 * Creates a new file download element from data
	 * @param {string | null} name File name
	 * @return {HTMLAnchorElement} New file download element
	 */
	public toDownloadElement(name: string | null = null): HTMLAnchorElement {
		const data: Blob | string = Array.isArray(this.content) || typeof this.content === 'object' ? JSON.stringify(this.content) : this.content;
		const blob: Blob = this.content instanceof Blob ? this.content : new Blob([data], { type: this.contentType });
		const fileUrl: string = window.URL.createObjectURL(blob);
		const element: HTMLAnchorElement = document.createElement('a');
		element.href = fileUrl;
		element.setAttribute('download', name ?? this.name);
		return element;
	}

}

/**
 * JSON value
 */
export type JsonValue = string | number | null;

/**
 * JSON object
 */
export interface Json {
	[propName: string]: JsonValue | JsonValue[] | Json
}

/**
 * Time format (12h or 24h)
 */
export enum TimeFormat {
	/// 12-hour format (AM/PM)
	Hour12 = '12h',
	/// 24-hour format
	Hour24 = '24h',
}
