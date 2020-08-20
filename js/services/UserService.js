import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

const API_URL = '//' + window.location.host + '/api/v0/';

class UserService {
	getInfo() {
		return axios.get(API_URL + 'user', {headers: authorizationHeader()});
	}
}

export default new UserService();
