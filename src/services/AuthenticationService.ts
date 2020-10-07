import axios, {AxiosResponse} from 'axios';

/**
 * User credentials
 */
export class UserCredentials {

	/**
	 * Username
	 */
	public username: string;

	/**
	 * Password
	 */
	public password: string;

	/**
	 * Constructor
	 * @param username Username
	 * @param password Password
	 */
	public constructor(username: string, password: string) {
		this.username = username;
		this.password = password;
	}

}

/**
 * User
 */
export interface User {

	/**
	 * User ID
	 */
	id: number;

	/**
	 * Username
	 */
	username: string;

	/**
	 * User role
	 */
	role: string;

	/**
	 * User language
	 */
	language: string;

	/**
	 * User token
	 */
	token: string;

}

/**
 * Authentication service
 */
class AuthenticationService {

	/**
	 * Signs in the user
	 * @param credentials User credentials
	 */
	login(credentials: UserCredentials): Promise<User> {
		return axios.post('user/signIn', credentials)
			.then((response: AxiosResponse): User => {
				return response.data as User;
			});
	}
}

export default new AuthenticationService();
