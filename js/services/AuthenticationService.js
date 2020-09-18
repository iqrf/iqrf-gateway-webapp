import axios from 'axios';

/**
 * Authentication service
 */
class AuthenticationService {
	/**
	 * Signs in the user (REST API)
	 * @param username Username
	 * @param password Password
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	apiLogin(username, password) {
		let data = {
			username: username,
			password: password
		};
		return axios.post('user/signIn', data);
	}

	/**
	 * Signs in the user (Nette login)
	 * @param username Username
	 * @param password Password
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	netteLogin(username, password) {
		const data = new URLSearchParams();
		data.append('username', username);
		data.append('password', password);
		data.append('remember', 'on');
		data.append('send', 'Sign+in');
		data.append('_do', 'signInForm-submit');
		return axios.post('//' + window.location.host + '/sign/in', data);
	}

	/**
	 * Signs in the user
	 * @param username Username
	 * @param password Password
	 * @returns {Promise.constructor|Promise<T>}
	 */
	login(username, password) {
		return Promise.all([
			this.apiLogin(username, password),
			this.netteLogin(username, password)
		]);
	}

	/**
	 * Signs out the user (Nette)
	 * @returns {Promise<AxiosResponse<any>>}
	 */
	logout() {
		return axios.get('//' + window.location.host + '/sign/out');
	}
}

export default new AuthenticationService();
