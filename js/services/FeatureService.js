import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

class FeatureService {
	fetchAll() {
		return axios.get('features', {headers: authorizationHeader()});
	}
}

export default new FeatureService();
