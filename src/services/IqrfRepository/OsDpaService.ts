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
import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '@/helpers/authorizationHeader';
import {IIqrfRepositoryConfig} from '@/interfaces/Config/Misc';
import IqrfRepositoryConfigService from './IqrfRepositoryConfigService';
import store from '@/store';

/**
 * OS & DPA version entity
 */
export class OsDpaVersion {

	/**
	 * IQRF OS build
	 * @private
	 */
	private readonly osBuild: string;

	/**
	 * IQRF OS version
	 * @private
	 */
	private readonly osVersion: string;

	/**
	 * DPA version
	 * @private
	 */
	private readonly dpaVersion: string;

	/**
	 * DPA version notes
	 * @private
	 */
	private readonly notes: string;

	/**
	 * DPA files download path
	 * @private
	 */
	private readonly downloadPath: string;

	/**
	 * DPA attributes
	 * @private
	 */
	private readonly dpaAttributes: number;

	/**
	 * DPA version entity
	 * @param {string} osBuild IQRF OS build
	 * @param {string} osVersion IQRF OS version
	 * @param {string} dpaVersion Raw DPA version
	 * @param {string} notes DPA version notes
	 * @param {string} downloadPath DPA files download path
	 * @param {number} dpaAttributes DPA attributes
	 */
	public constructor(osBuild: string, osVersion: string, dpaVersion: string, notes: string, downloadPath: string, dpaAttributes: number) {
		this.osBuild = osBuild;
		this.osVersion = osVersion;
		this.dpaVersion = dpaVersion;
		this.notes = notes;
		this.downloadPath = downloadPath;
		this.dpaAttributes = dpaAttributes;
	}

	/**
	 * Returns IQRF OS build
	 * @return IQRF OS build
	 */
	public getOsBuild(): string {
		return this.osBuild;
	}

	/**
	 * Returns IQRF OS version
	 * @return IQRF OS version
	 */
	public getOsVersion(): string {
		return this.osVersion;
	}

	/**
	 * Returns DPA version
	 * @param pretty Pretty formatting
	 * @return DPA version
	 */
	public getDpaVersion(pretty: boolean): string {
		if (!pretty) {
			return this.dpaVersion;
		}
		const versionInt = Number.parseInt(this.dpaVersion, 16);
		const major = versionInt >> 8;
		const minor = versionInt & 0xff;
		return major.toString() + '.' + minor.toString(16).padStart(2, '0') + this.getDpaAttributesString();
	}

	/**
	 * Returns DPA version notes
	 * @return DPA version notes
	 */
	public getNotes(): string {
		return this.notes;
	}

	/**
	 * Returns DPA files download path
	 * @return DPA files download path
	 */
	public getDownloadPath(): string {
		return this.downloadPath;
	}

	/**
	 * Returns DPA attributes
	 * @returns DPA attributes
	 */
	public getDpaAttributes(): number {
		return this.dpaAttributes;
	}

	/**
	 * Returns DPA version properties string based on attributes
	 * @returns DPA version properties
	 */
	public getDpaAttributesString(): string {
		switch (this.dpaAttributes) {
			case 0:
				return '';
			case 1:
				return ' (Beta version)';
			case 2:
				return ' (Obsolete)';
			case 3:
				return ' (Beta version, Obsolete)';
			default:
				return '';
		}
	}

}

/**
 * TR RF mode enum
 */
export enum RFMode {
	STD = 'STD',
	LP = 'LP'
}

/**
 * IQRF Repository DPA service
 */
class OsDpaService {

	/**
	 * Retrieves available DPA versions
	 * @param osBuild Current IQRF OS build
	 */
	public async getVersions(osBuild: string): Promise<OsDpaVersion[]> {
		let baseUrl = 'https://repository.iqrfalliance.org/api';
		const params = {params: {os: osBuild}};
		let config = store.getters['repository/configuration'];
		if (!config) {
			await IqrfRepositoryConfigService.get()
				.then((repositoryConfig: IIqrfRepositoryConfig) => {
					config = repositoryConfig;
				});
		}
		if (!config) {
			baseUrl = config.apiEndpoint;
			if (config.credentials.username === null) {
				params.params['dpaBeta'] = false;
			}
		}
		return axios.get(baseUrl + '/osdpa/', params)
			.then((response: AxiosResponse) => {
				const versions: OsDpaVersion[] = [];
				for (const version of response.data) {
					versions.push(new OsDpaVersion(version.os, version.osVersion, version.dpa, version.notes, version.downloadPath, version.dpaAttributes));
				}
				return versions;
			});
	}

	/**
	 * Retrieves name of DPA file to upload
	 * @param metadata DPA file metadata
	 */
	public getDpaFile(metadata: any): Promise<AxiosResponse> {
		return axios.post('iqrf/dpaFile', metadata, {headers: authorizationHeader()});
	}

}

export default new OsDpaService();
