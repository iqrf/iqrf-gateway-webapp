/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

import {IGwBackup} from '../interfaces/backup';
import {IHostname} from '../interfaces/gatewayInfo';
import {ISystemdJournal} from '../interfaces/systemdJournal';

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
	 * Retrieves available logs
	 */
	getAvailableLogs(): Promise<AxiosResponse> {
		return axios.get('gateway/logs', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves a service log
	 * @param service service name
	 * @returns
	 */
	getServiceLog(service: string): Promise<AxiosResponse> {
		return axios.get('gateway/logs/' + service, {headers: authorizationHeader(), timeout: 60000});
	}

	/**
	 * Retrieves a ZIP archive with IQRF Gateway Daemon's log files
	 */
	getLogArchive(): Promise<AxiosResponse> {
		return axios.get('gateway/logs/export', {headers: authorizationHeader(), responseType: 'blob'});
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
	 * Retrieves systemd journal configuration
	 */
	getSystemdJournalConfig(): Promise<AxiosResponse> {
		return axios.get('gateway/journal/config', {headers: authorizationHeader()});
	}

	/**
	 * Saves systemd journal configuration
	 * @param {ISystemdJournal} config New configuration
	 */
	saveSystemdJournalConfig(config: ISystemdJournal): Promise<AxiosResponse> {
		return axios.post('gateway/journal/config', config, {headers: authorizationHeader()});
	}

	/**
	 * Sets hostname
	 * @param {IHostname} config Hostname configuration
	 */
	setHostname(config: IHostname): Promise<AxiosResponse> {
		return axios.post('gateway/hostname', config, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves NTP configuration
	 */
	getNtp(): Promise<AxiosResponse> {
		return axios.get('gateway/ntp', {headers: authorizationHeader()});
	}

	/**
	 * Saves NTP configuration
	 * @param config NTP configuration
	 */
	setNtp(config: Array<string>): Promise<AxiosResponse> {
		return axios.put('gateway/ntp', config, {headers: authorizationHeader()});
	}

	/**
	 * Attempts to synchronize system clock
	 */
	ntpSync(): Promise<AxiosResponse> {
		return axios.post('gateway/ntp/sync', null, {headers: authorizationHeader(), timeout: 60000});
	}

	/**
	 * Saves SSH keys
	 * @param {Array<string>} keys SSh keys
	 */
	saveSshKeys(keys: Array<string>): Promise<AxiosResponse> {
		return axios.post('gateway/ssh/keys', keys, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves gateway backup data
	 * @param params Backup parameters
	 */
	backup(params: IGwBackup): Promise<AxiosResponse> {
		return axios.post('gateway/backup', params, {headers: authorizationHeader(), responseType: 'arraybuffer'});
	}

	/**
	 * Restores gateway from backup data
	 * @param archive Backup archive
	 */
	restore(archive: File): Promise<AxiosResponse> {
		const url = 'gateway/restore';
		const headers = {
			...authorizationHeader(),
			'Content-Type': archive.type,
		};
		return axios.post(url, archive, {headers: headers});
	}

}

export default new GatewayService();
