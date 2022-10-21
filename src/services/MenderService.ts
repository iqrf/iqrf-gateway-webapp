import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '@/helpers/authorizationHeader';
import {IRemount} from '@/interfaces/Maintenance/Mender';

/**
 * Mender update service
 */
class MenderService {

	/**
	 * Upload Mender server certificate
	 * @param {FormData} file Certificate to upload
	 * @returns {Promise<AxiosResponse>} Axios response promise
	 */
	uploadCertificate(file: FormData): Promise<AxiosResponse> {
		return axios.post('config/mender/cert', file, {headers: authorizationHeader()});
	}

	/**
	 * Installs data from mender artifact
	 * @param {FormData} file File to install
	 * @returns {Promise<AxiosResponse>} Axios response promise
	 */
	install(file: FormData): Promise<AxiosResponse> {
		return axios.post('mender/install', file, {headers: authorizationHeader(), timeout: 600000});
	}

	/**
	 * Commits currently installed artifact update
	 * @returns {Promise<AxiosResponse>} Axios response promise
	 */
	commit(): Promise<AxiosResponse> {
		return axios.post('mender/commit', null, {headers: authorizationHeader()});
	}

	/**
	 * Rolls currently installed artifact update back
	 * @returns {Promise<AxiosResponse>} Axios response promise
	 */
	rollback(): Promise<AxiosResponse> {
		return axios.post('mender/rollback', null, {headers: authorizationHeader()});
	}

	/**
	 * Remounts filesystem with specified mode
	 * @param {IRemount} mode Mode
	 * @returns {Promise<AxiosResponse>} Axios response promise
	 */
	remount(mode: IRemount): Promise<AxiosResponse> {
		return axios.post('mender/remount', mode, {headers: authorizationHeader()});
	}
}

export default new MenderService();
