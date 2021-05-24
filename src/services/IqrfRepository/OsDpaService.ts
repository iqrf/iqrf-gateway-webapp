import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../../helpers/authorizationHeader';

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
	 * DPA version entity
	 * @param osBuild IQRF OS build
	 * @param osVersion IQRF OS version
	 * @param dpaVersion Raw DPA version
	 * @param notes DPA version notes
	 * @param downloadPath DPA files download path
	 */
	public constructor(osBuild: string, osVersion: string, dpaVersion: string, notes: string, downloadPath: string) {
		this.osBuild = osBuild;
		this.osVersion = osVersion;
		this.dpaVersion = dpaVersion;
		this.notes = notes;
		this.downloadPath = downloadPath;
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
		return major.toString() + '.' + minor.toString(16).padStart(2, '0');
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
	public getVersions(osBuild: string): Promise<OsDpaVersion[]> {
		const url = 'https://repository.iqrfalliance.org/api/osdpa/';
		return axios.get(url, {params: {os: osBuild}})
			.then((response: AxiosResponse) => {
				const versions: OsDpaVersion[] = [];
				for (const version of response.data) {
					versions.push(new OsDpaVersion(version.os, version.osVersion, version.dpa, version.notes, version.downloadPath));
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