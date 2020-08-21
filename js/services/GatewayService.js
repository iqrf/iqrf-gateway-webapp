import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

class GatewayService {
	getDiagnosticsArchive() {
		return axios.get('diagnostics', {headers: authorizationHeader(), responseType: 'blob'});
	}
	getInfo() {
		return axios.get('gateway/info', {headers: authorizationHeader()});
	}
	getLatestLog() {
		return axios.get('gateway/log', {headers: authorizationHeader()});
	}
	getLogArchive() {
		return axios.get('gateway/logs', {headers: authorizationHeader(), responseType: 'blob'});
	}
}

export default new GatewayService();
