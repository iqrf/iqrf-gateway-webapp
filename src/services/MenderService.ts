/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
import {authorizationHeader} from '@/helpers/authorizationHeader';
import {MenderRemount} from '@/interfaces/Maintenance/Mender';

/**
 * Mender update service
 */
class MenderService {

	/**
	 * Upload Mender server certificate
	 * @param {FormData} file Certificate to upload
	 * @returns {Promise<AxiosResponse>} Axios response promise
	 */
	uploadCertificate(file: FormData): Promise<AxiosResponse> {
		return axios.post('config/mender/cert', file, {headers: authorizationHeader()});
	}

	/**
	 * Installs data from mender artifact
	 * @param {FormData} file File to install
	 * @returns {Promise<AxiosResponse>} Axios response promise
	 */
	install(file: FormData): Promise<AxiosResponse> {
		return axios.post('mender/install', file, {headers: authorizationHeader(), timeout: 600000});
	}

	/**
	 * Commits currently installed artifact update
	 * @returns {Promise<AxiosResponse>} Axios response promise
	 */
	commit(): Promise<AxiosResponse> {
		return axios.post('mender/commit', null, {headers: authorizationHeader()});
	}

	/**
	 * Rolls currently installed artifact update back
	 * @returns {Promise<AxiosResponse>} Axios response promise
	 */
	rollback(): Promise<AxiosResponse> {
		return axios.post('mender/rollback', null, {headers: authorizationHeader()});
	}

	/**
	 * Remounts filesystem with specified mode
	 * @param {MenderRemount} mode Mode
	 * @returns {Promise<AxiosResponse>} Axios response promise
	 */
	remount(mode: MenderRemount): Promise<AxiosResponse> {
		return axios.post('mender/remount', mode, {headers: authorizationHeader()});
	}
}

export default new MenderService();
