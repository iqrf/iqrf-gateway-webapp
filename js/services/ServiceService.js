import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

class ServiceService {
	disable(name) {
		return axios.post('services/' + name + '/disable', null, {headers: authorizationHeader()});
	}

	enable(name) {
		return axios.post('services/' + name + '/enable', null, {headers: authorizationHeader()});
	}

	getStatus(name) {
		return axios.get('services/' + name, {headers: authorizationHeader()});
	}

	restart(name) {
		return axios.post('services/' + name + '/restart', null, {headers: authorizationHeader()});
	}

	start(name) {
		return axios.post('services/' + name + '/start', null, {headers: authorizationHeader()});
	}

	stop(name) {
		return axios.post('services/' + name + '/stop', null, {headers: authorizationHeader()});
	}
}

export default new ServiceService();
