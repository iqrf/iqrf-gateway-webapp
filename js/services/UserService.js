import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * User service
 */
class UserService {

	/**
	 * Deletes the user
	 * @param {Number} id User ID
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	delete(id) {
		return axios.delete('users/' + id, {headers: authorizationHeader()});
	}

	/**
	 * Edits the user
	 * @param {Number} id User ID
	 * @param {Object} user User settings
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	edit(id, user) {
		return axios.put('users/' + id, user, {headers: authorizationHeader()});
	}

	/**
	 * Returns information about the logged in user
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	getInfo() {
		return axios.get('user', {headers: authorizationHeader()});
	}

	/**
	 * Lists all users
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	list() {
		return axios.get('users', {headers: authorizationHeader()});
	}
}

export default new UserService();
