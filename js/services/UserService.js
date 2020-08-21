import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

class UserService {
	getInfo() {
		return axios.get('user', {headers: authorizationHeader()});
	}
}

export default new UserService();
