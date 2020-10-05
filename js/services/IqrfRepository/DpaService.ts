import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../../helpers/authorizationHeader';

/**
 * DPA version entity
 */
export class DpaVersion {

	/**
	 * IQRF OS build
	 * @private
	 */
	private readonly osBuild: string;

	/**
	 * DPA version
	 * @private
	 */
	private readonly version: string;

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
	 * @param osBuild Supported IQRF OS build
	 * @param version Raw DPA version
	 * @param notes DPA version notes
	 * @param downloadPath DPA files download path
	 */
	public constructor(osBuild: string, version: string, notes: string, downloadPath: string) {
		this.osBuild = osBuild;
		this.version = version;
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
	 * Returns DPA version
	 * @param pretty Pretty formatting
	 * @return DPA version
	 */
	public getVersion(pretty: boolean): string {
		if (!pretty) {
			return this.version;
		}
		const versionInt = Number.parseInt(this.version, 16);
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
class DpaService {

	/**
	 * Retrieves available DPA versions
	 * @param osBuild Current IQRF OS build
	 */
	public getVersions(osBuild: string): Promise<DpaVersion[]> {
		const url = 'https://repository.iqrfalliance.org/api/osdpa/';
		return axios.get(url, {params: {os: osBuild}})
			.then((response: AxiosResponse) => {
				const versions: DpaVersion[] = [];
				for (const version of response.data) {
					versions.push(new DpaVersion(version.os, version.dpa, version.notes, version.downloadPath));
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

export default new DpaService();
