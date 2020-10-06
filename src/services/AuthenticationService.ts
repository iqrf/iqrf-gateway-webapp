import axios, {AxiosResponse} from 'axios';

/**
 * Authentication service
 */
class AuthenticationService {

	/**
	 * Signs in the user
	 * @param username Username
	 * @param password Password
	 */
	login(username: string, password: string): Promise<AxiosResponse> {
		const data = {
			username: username,
			password: password
		};
		return axios.post('user/signIn', data);
	}
}

export default new AuthenticationService();
