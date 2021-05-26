import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Mender update service
 */
class MenderService {
	/**
	 * Installs data from mender artifact
	 * @param {FormData} file File to install
	 * @returns {Promise<AxiosResponse>} Axios response promise
	 */
	install(file: FormData): Promise<AxiosResponse> {
		return axios.post('mender/install', file, {headers: authorizationHeader()});
	}
}

export default new MenderService();
