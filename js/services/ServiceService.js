import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * System service service
 */
class ServiceService {
	/**
	 * Disables the service
	 * @param name Service name
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	disable(name) {
		return axios.post('services/' + name + '/disable', null, {headers: authorizationHeader()});
	}

	/**
	 * Enables the service
	 * @param name Service name
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	enable(name) {
		return axios.post('services/' + name + '/enable', null, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves the service status
	 * @param name Service name
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	getStatus(name) {
		return axios.get('services/' + name, {headers: authorizationHeader()});
	}

	/**
	 * Restarts the service
	 * @param name Service name
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	restart(name) {
		return axios.post('services/' + name + '/restart', null, {headers: authorizationHeader()});
	}

	/**
	 * Starts the service
	 * @param name Service name
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	start(name) {
		return axios.post('services/' + name + '/start', null, {headers: authorizationHeader()});
	}

	/**
	 * Stops the service
	 * @param name Service name
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	stop(name) {
		return axios.post('services/' + name + '/stop', null, {headers: authorizationHeader()});
	}
}

export default new ServiceService();
