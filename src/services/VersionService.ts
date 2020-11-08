import store from '../store';
import {authorizationHeader} from '../helpers/authorizationHeader';
import {WebSocketOptions} from '../store/modules/webSocketClient.module';
import axios from 'axios';
import {AxiosResponse} from 'axios';

/**
 * Version service
 */
class VersionService {

	/**
	 * Retrieves IQRF Gateway Daemon version
	 * @param options WebSocket request options
	 */
	getDaemonVersion(options: WebSocketOptions): Promise<string> {
		options.request = {
			'mType': 'mngDaemon_Version',
			'data': {
				'returnVerbose': true,
			},
		};
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Retrieves IQRF Gateway Daemon version via the REST API
	 */
	getDaemonVersionRest(): Promise<AxiosResponse> {
		return axios.get('/version/daemon', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves IQRF Gateway Webapp version via the REST API
	 */
	getWebappVersionRest(): Promise <AxiosResponse> {
		return axios.get('version/webapp', {headers: authorizationHeader()});
	}
}

export default new VersionService();
