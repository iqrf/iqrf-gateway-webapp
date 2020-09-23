import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Daemon components configuration service
 */
class ComponentConfigService {
	/**
	 * Creates instance configuration of a component
	 * @param componentName name of daemon component
	 * @param config new component instance configuration
	 */
	createConfig(componentName: string, config: any): Promise<AxiosResponse> {
		return axios.post('config/daemon/' + encodeURIComponent(componentName), config, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves configuration of a component
	 * @param componentName name of daemon component
	 */
	getConfig(componentName: string): Promise<AxiosResponse> {
		return axios.get('config/daemon/' + encodeURIComponent(componentName), {headers: authorizationHeader()});
	}

	/**
	 * Saves new instance configuration of a component
	 * @param componentName name of daemon component
	 * @param instanceName name of component instance
	 * @param config new component instance configuration
	 */
	saveConfig(componentName: string, instanceName: string, config: any): Promise<AxiosResponse> {
		return axios.put('config/daemon/' + encodeURIComponent(componentName) + '/' + encodeURIComponent(instanceName), config, {headers: authorizationHeader()});
	}

	/**
	 * Exports daemon configuration
	 */
	exportConfig(): Promise<AxiosResponse> {
		return axios.get('config/daemon/migration/export', {headers: authorizationHeader(), responseType: 'arraybuffer'});
	}

	/**
	 * Imports daemon configuration
	 */
	importConfig(config: any): Promise<AxiosResponse> {
		return axios.post('config/daemon/migration/import', config, {headers: authorizationHeader()});
	}
}

export default new ComponentConfigService();