import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

class PixlaService {
	getToken() {
		return axios.get('pixla', {headers: authorizationHeader()});
	}
}

export default new PixlaService();
