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
import store from '@/store';
import {FileFormat} from '@/iqrfNet/fileFormat';
import axios from 'axios';
import {AxiosResponse} from 'axios';
import {authorizationHeader} from '@/helpers/authorizationHeader';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

/**
 * Native upload service
 */
class NativeUploadService {
	/**
	 * Sends Daemon API request to upload file
	 * @param filePath path to file
	 * @param format file format
	 * @param timeout Timeout in milliseconds
	 * @param message Timeout message
	 * @param callback Timeout callback
	 * @return Message ID
	 */
	upload(filePath: string, format: FileFormat, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'mngDaemon_Upload',
			'data': {
				'req': {
					'fileName': filePath,
					'target': format,
				},
				'returnVerbose': true,
			},
		};
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Uploads file via rest API
	 * @param data file data and metadata
	 */
	uploadREST(data: FormData): Promise<AxiosResponse> {
		return axios.post('iqrf/upload', data, {headers: authorizationHeader()});
	}
}

export default new NativeUploadService();
