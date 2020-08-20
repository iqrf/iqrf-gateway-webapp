import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

const API_URL = '//' + window.location.host + '/api/v0/';

class ServiceService {
	disable(name) {
		return axios.post(API_URL + 'services/' + name + '/disable',{headers: authorizationHeader()});
	}

	enable(name) {
		return axios.post(API_URL + 'services/' + name + '/enable',{headers: authorizationHeader()});
	}

	getStatus(name) {
		return axios.get(API_URL + 'services/' + name, {headers: authorizationHeader()});
	}

	restart(name) {
		return axios.post(API_URL + 'services/' + name + '/restart',{headers: authorizationHeader()});
	}

	start(name) {
		return axios.post(API_URL + 'services/' + name + '/disable',{headers: authorizationHeader()});
	}

	stop(name) {
		return axios.post(API_URL + 'services/' + name + '/disable',{headers: authorizationHeader()});
	}
}

export default new ServiceService();
