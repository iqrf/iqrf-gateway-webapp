import axios, {AxiosResponse} from 'axios';

/**
 * Authentication service
 */
class AuthenticationService {
	/**
	 * Signs in the user (REST API)
	 * @param username Username
	 * @param password Password
	 */
	apiLogin(username: string, password: string): Promise<AxiosResponse> {
		const data = {
			username: username,
			password: password
		};
		return axios.post('user/signIn', data);
	}

	/**
	 * Signs in the user (Nette login)
	 * @param username Username
	 * @param password Password
	 */
	netteLogin(username: string, password: string): Promise<AxiosResponse> {
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
	 */
	login(username: string, password: string): Promise<AxiosResponse[]> {
		return Promise.all([
			this.apiLogin(username, password),
			this.netteLogin(username, password)
		]);
	}

	/**
	 * Signs out the user (Nette)
	 */
	logout(): Promise<AxiosResponse> {
		return axios.get('//' + window.location.host + '/sign/out');
	}
}

export default new AuthenticationService();
