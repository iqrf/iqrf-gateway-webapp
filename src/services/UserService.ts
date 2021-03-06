/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
	 * Adds the new user
	 * @param username Username
	 * @param password Password
	 * @param language Language
	 * @param role Role
	 */
	add(username: string, password: string, language: string, role: string): Promise<AxiosResponse> {
		const body = {
			username: username,
			password: password,
			language: language,
			role: role,
		};
		return axios.post('users/', body, {headers: authorizationHeader()});
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
