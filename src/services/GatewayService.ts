import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Root password interface
 */
export interface RootPassword {
	password: string,
}

/**
 * Gateway service
 */
class GatewayService {
	/**
	 * Retrieves a ZIP archive with diagnostics data
	 */
	getDiagnosticsArchive(): Promise<AxiosResponse> {
		return axios.get('diagnostics', {headers: authorizationHeader(), responseType: 'blob'});
	}

	/**
	 * Retrieves information about the gateway
	 */
	getInfo(): Promise<AxiosResponse> {
		return axios.get('gateway/info', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves the latest IQRF gateway Daemon's log file
	 */
	getLatestLog(): Promise<AxiosResponse> {
		return axios.get('gateway/log', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves a ZIP archive with IQRF Gateway Daemon's log files
	 */
	getLogArchive(): Promise<AxiosResponse> {
		return axios.get('gateway/logs', {headers: authorizationHeader(), responseType: 'blob'});
	}

	/**
	 * Performs power off
	 */
	performPowerOff(): Promise<AxiosResponse> {
		return axios.post('gateway/poweroff', null, {headers: authorizationHeader()});
	}

	/**
	 * Performs reboot
	 */
	performReboot(): Promise<AxiosResponse> {
		return axios.post('gateway/reboot', null, {headers: authorizationHeader()});
	}

	/**
	 * Sets gateway root password
	 * @param {RootPassword} data New root gateway password
	 */
	setRootPass(data: RootPassword): Promise<AxiosResponse> {
		return axios.put('gateway/rootpass', data, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves systemd log configuration
	 */
	systemdLog(): Promise<AxiosResponse> {
		return axios.get('gateway/syslog', {headers: authorizationHeader()});
	}

	/**
	 * Enables systemd log persistence
	 */
	enablePersistence(): Promise<AxiosResponse> {
		return axios.post('gateway/syslog/persistence/enable', null, {headers: authorizationHeader()});
	}

	/**
	 * Enables systemd log persistence
	 */
	disablePersistence(): Promise<AxiosResponse> {
		return axios.post('gateway/syslog/persistence/disable', null, {headers: authorizationHeader()});
	}
}

export default new GatewayService();
