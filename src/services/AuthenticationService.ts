/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
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
 * User account states
 */
export enum AccountState {

	/**
	 * Unverified e-mail address
	 */
	UNVERIFIED = 'unverified',

	/**
	 * verified e-mail address
	 */
	VERIFIED = 'verified',

}

/**
 * User language
 */
export enum UserLanguage {

	/**
	 * English
	 */
	ENGLISH = 'en'

}

/**
 * User roles
 */
export enum UserRole {
	/**
	 * Admin user
	 */
	ADMIN = 'admin',

	/**
	 * Normal user
	 */
	NORMAL = 'normal',

	/**
	 * Basic user with user management
	 */
	BASICADMIN = 'basicadmin',

	/**
	 * Basic user
	 */
	BASIC = 'basic',
}

/**
 * User information with JWT
 */
export interface User extends UserInfo {

	/**
	 * User token
	 */
	token: string;
}

/**
 * User information without JWT
 */
export interface UserInfo extends IUserBase {
	/**
	 * User ID
	 */
	id: number;

	/**
	 * Account state
	 */
	state: AccountState;
}

/**
 * Simplified user edit interface
 */
export interface IUserBase {
	/**
	 * Username
	 */
	username: string;

	/**
	 * E-mail address
	 */
	email: string|null;

	/**
	 * User language
	 */
	language: UserLanguage;

	/**
	 * User role
	 */
	role: UserRole;
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
