import store from '../store';
import {FileFormat} from '../iqrfNet/fileFormat';
import axios from 'axios';
import { AxiosResponse } from 'axios';
import { authorizationHeader } from '../helpers/authorizationHeader';

/**
 * Native upload service
 */
class NativeUploadService {
	/**
	 * Sends Daemon API request to upload file
	 * @param filePath path to file
	 * @param format file format
	 */
	upload(filePath: string, format: FileFormat) {
		return store.dispatch('sendRequest', {
			'mType': 'mngDaemon_Upload',
			'data': {
				'req': {
					'fileName': filePath,
					'target': format,
				},
				'returnVerbose': true,
			},
		});
	}

	/**
	 * Uploads file via rest API
	 * @param data file data and metadata
	 */
	uploadREST(data: any): Promise<AxiosResponse> {
		return axios.post('iqrf/upload', data, {headers: authorizationHeader()});
	}
}

export default new NativeUploadService();
