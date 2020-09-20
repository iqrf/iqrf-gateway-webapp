import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * User service
 */
class UserService {

	/**
	 * Changes password
	 * @param oldPassword Old password
	 * @param newPassword New password
	 */
	changePassword(oldPassword: string, newPassword: string): Promise<AxiosResponse> {
		const body = {
			old: oldPassword,
			new: newPassword
		};
		return axios.put('user/password', body, {headers: authorizationHeader()});
	}

	/**
	 * Deletes the user
	 * @param id User ID
	 */
	delete(id: number): Promise<AxiosResponse> {
		return axios.delete('users/' + id, {headers: authorizationHeader()});
	}

	/**
	 * Edits the user
	 * @param id User ID
	 * @param user User settings
	 */
	edit(id: number, user: any): Promise<AxiosResponse> {
		return axios.put('users/' + id, user, {headers: authorizationHeader()});
	}

	/**
	 * Returns the user
	 * @param id User ID
	 */
	get(id: number): Promise<AxiosResponse> {
		return axios.get('users/' + id, {headers: authorizationHeader()});
	}

	/**
	 * Returns information about the logged in user
	 */
	getLoggedIn(): Promise<AxiosResponse> {
		return axios.get('user', {headers: authorizationHeader()});
	}

	/**
	 * Lists all users
	 */
	list(): Promise<AxiosResponse> {
		return axios.get('users', {headers: authorizationHeader()});
	}
}

export default new UserService();
