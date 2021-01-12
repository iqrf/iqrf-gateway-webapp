import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';
import { IChangeInterface } from '../interfaces/daemonComponent';

/**
 * Daemon configuration service
 */
class DaemonConfigurationService {

	/**
	 * Creates a new component
	 * @param configuration Daemon component configuration
	 */
	createComponent(configuration: any): Promise<AxiosResponse> {
		const url = 'config/daemon/';
		return axios.post(url, configuration, {headers: authorizationHeader()});
	}

	/**
	 * Creates a new component instance
	 * @param component Daemon component name
	 * @param configuration Daemon component instance configuration
	 */
	createInstance(component: string, configuration: any): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component);
		return axios.post(url, configuration, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves the component configuration and instances
	 * @param component Daemon component name
	 */
	getComponent(component: string): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component);
		return axios.get(url, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves the component instance configuration and instances
	 * @param component Daemon component name
	 * @param instance Daemon component instance name
	 */
	getInstance(component: string, instance: string): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component) + '/' + encodeURIComponent(instance);
		return axios.get(url, {headers: authorizationHeader()});
	}

	/**
	 * Deletes the component
	 * @param component Daemon component name
	 */
	deleteComponent(component: string): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component);
		return axios.delete(url, {headers: authorizationHeader()});
	}

	/**
	 * Deletes the component instance
	 * @param component Daemon component name
	 * @param instance Daemon component instance name
	 */
	deleteInstance(component: string, instance: string): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component) + '/' + encodeURIComponent(instance);
		return axios.delete(url, {headers: authorizationHeader()});
	}

	/**
	 * Exports daemon configuration
	 */
	export(): Promise<AxiosResponse> {
		const url = 'config/daemon/migration/export';
		return axios.get(url, {headers: authorizationHeader(), responseType: 'arraybuffer'});
	}

	/**
	 * Imports daemon configuration
	 * @param config daemon configuration
	 */
	import(config: File): Promise<AxiosResponse> {
		const url = 'config/daemon/migration/import';
		const headers = {
			...authorizationHeader(),
			'Content-Type': config.type
		};
		return axios.post(url, config, {headers: headers});
	}

	/**
	 * Updates the component configuration
	 * @param component Daemon component name
	 * @param configuration Daemon component configuration
	 */
	updateComponent(component: string, configuration: any): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component);
		return axios.put(url, configuration, {headers: authorizationHeader()});
	}

	/**
	 * Updates the component instance configuration
	 * @param component Daemon component name
	 * @param instance Daemon component instance name
	 * @param configuration Daemon component instance configuration
	 */
	updateInstance(component: string, instance: string, configuration: any): Promise<AxiosResponse> {
		const url = 'config/daemon/' + encodeURIComponent(component) + '/' + encodeURIComponent(instance);
		return axios.put(url, configuration, {headers: authorizationHeader()});
	}

	/**
	 * Changes IQRF interfaces
	 * @param data Interface configuration
	 */
	changeInterface(data: Array<IChangeInterface>): Promise<AxiosResponse> {
		return axios.patch('config/daemon/interface', data, {headers: authorizationHeader()});
	}

}

export default new DaemonConfigurationService();
