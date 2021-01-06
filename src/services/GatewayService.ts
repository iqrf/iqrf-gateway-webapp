import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

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
	 * Troubleshoots gateway features and services
	 */
	troubleshoot(): Promise<AxiosResponse> {
		return axios.get('gateway/troubleshoot', {headers: authorizationHeader()});
	}
}

export default new GatewayService();
