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
	 * Sets default gateway user password
	 * @param {RootPassword} data New password
	 */
	setGatewayPassword(data: RootPassword): Promise<AxiosResponse> {
		return axios.put('gateway/password', data, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves systemd log configuration
	 */
	systemdLog(): Promise<AxiosResponse> {
		return axios.get('gateway/journal', {headers: authorizationHeader()});
	}

	/**
	 * Enables systemd log persistence
	 */
	enablePersistence(): Promise<AxiosResponse> {
		return axios.post('gateway/journal/persistence/enable', null, {headers: authorizationHeader()});
	}

	/**
	 * Enables systemd log persistence
	 */
	disablePersistence(): Promise<AxiosResponse> {
		return axios.post('gateway/journal/persistence/disable', null, {headers: authorizationHeader()});
	}
}

export default new GatewayService();
