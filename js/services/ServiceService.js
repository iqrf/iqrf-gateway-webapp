import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

const API_URL = '//' + window.location.host + '/api/v0/';

class ServiceService {
	disable(name) {
		return axios.post(API_URL + 'services/' + name + '/disable', null, {headers: authorizationHeader()});
	}

	enable(name) {
		return axios.post(API_URL + 'services/' + name + '/enable', null, {headers: authorizationHeader()});
	}

	getStatus(name) {
		return axios.get(API_URL + 'services/' + name, {headers: authorizationHeader()});
	}

	restart(name) {
		return axios.post(API_URL + 'services/' + name + '/restart', null, {headers: authorizationHeader()});
	}

	start(name) {
		return axios.post(API_URL + 'services/' + name + '/start', null, {headers: authorizationHeader()});
	}

	stop(name) {
		return axios.post(API_URL + 'services/' + name + '/stop', null, {headers: authorizationHeader()});
	}
}

export default new ServiceService();
