import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * System service service
 */
class ServiceService {
	/**
	 * Disables the service
	 * @param name Service name
	 */
	disable(name: string): Promise<AxiosResponse> {
		return axios.post('services/' + name + '/disable', null, {headers: authorizationHeader()});
	}

	/**
	 * Enables the service
	 * @param name Service name
	 */
	enable(name: string): Promise<AxiosResponse> {
		return axios.post('services/' + name + '/enable', null, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves the service status
	 * @param name Service name
	 */
	getStatus(name: string): Promise<AxiosResponse> {
		return axios.get('services/' + name, {headers: authorizationHeader()});
	}

	/**
	 * Restarts the service
	 * @param name Service name
	 */
	restart(name: string): Promise<AxiosResponse> {
		return axios.post('services/' + name + '/restart', null, {headers: authorizationHeader()});
	}

	/**
	 * Starts the service
	 * @param name Service name
	 */
	start(name: string): Promise<AxiosResponse> {
		return axios.post('services/' + name + '/start', null, {headers: authorizationHeader()});
	}

	/**
	 * Stops the service
	 * @param name Service name
	 */
	stop(name: string): Promise<AxiosResponse> {
		return axios.post('services/' + name + '/stop', null, {headers: authorizationHeader()});
	}
}

export default new ServiceService();
