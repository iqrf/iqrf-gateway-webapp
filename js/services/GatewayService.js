import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * Gateway service
 */
class GatewayService {
	/**
	 * Retrieves a ZIP archive with diagnostics data
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	getDiagnosticsArchive() {
		return axios.get('diagnostics', {headers: authorizationHeader(), responseType: 'blob'});
	}

	/**
	 * Retrieves information about the gateway
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	getInfo() {
		return axios.get('gateway/info', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves the latest IQRF gateway Daemon's log file
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	getLatestLog() {
		return axios.get('gateway/log', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves a ZIP archive with IQRF Gateway Daemon's log files
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	getLogArchive() {
		return axios.get('gateway/logs', {headers: authorizationHeader(), responseType: 'blob'});
	}

	/**
	 * Performs power off
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	performPowerOff() {
		return axios.post('gateway/poweroff', null, {headers: authorizationHeader()});
	}

	/**
	 * Performs reboot
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	performReboot() {
		return axios.post('gateway/reboot', null, {headers: authorizationHeader()});
	}
}

export default new GatewayService();
