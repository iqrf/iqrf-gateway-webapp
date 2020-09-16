import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * User service
 */
class UserService {

	/**
	 * Changes password
	 * @param oldPassword Old password
	 * @param newPassword New password
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	changePassword(oldPassword, newPassword) {
		const body = {
			old: oldPassword,
			new: newPassword
		};
		return axios.put('user/password', body, {headers: authorizationHeader()});
	}

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
	 * Returns the user
	 * @param {Number} id User ID
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	get(id) {
		return axios.get('users/' + id, {headers: authorizationHeader()});
	}

	/**
	 * Returns information about the logged in user
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	getLoggedIn() {
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
