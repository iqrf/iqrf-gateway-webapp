import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

const API_URL = '//' + window.location.host + '/api/v0/';

class GatewayService {
	getDiagnosticsArchive() {
		return axios.get(API_URL + 'diagnostics', {headers: authorizationHeader(), responseType: 'blob'});
	}
	getInfo() {
		return axios.get(API_URL + 'gateway/info', {headers: authorizationHeader()});
	}
	getLatestLog() {
		return axios.get(API_URL + 'gateway/log', {headers: authorizationHeader()});
	}
	getLogArchive() {
		return axios.get(API_URL + 'gateway/logs', {headers: authorizationHeader(), responseType: 'blob'});
	}
}

export default new GatewayService();
