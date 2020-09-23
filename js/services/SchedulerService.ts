import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

class SchedulerService {
	/**
	 * Retrieve scheduler tasks
	 */
	getTasks(): Promise<AxiosResponse> {
		return axios.get('scheduler', {headers: authorizationHeader()});
	}
	
	/**
	 * Exports scheduler configuration
	 */
	exportConfig(): Promise<AxiosResponse> {
		return axios.get('scheduler/export', {headers: authorizationHeader(), responseType: 'arraybuffer'});
	}
	
	/**
	 * Import scheduler configuration
	 * @param config scheduler configuration
	 */
	importConfig(config: any): Promise<AxiosResponse> {
		return axios.post('scheduler/import', config, {headers: authorizationHeader()});
	}
}

export default new SchedulerService();