import axios from 'axios';

const API_URL = '//' + window.location.host + '/api/v0/';

class AuthenticationService {
	login(username, password) {
		let data = {
			username: username,
			password: password
		};
		return axios.post(API_URL + 'user/signIn', data)
			.then(response => {
				if (response.status === 200 && response.data.token) {
					localStorage.setItem('jwt', response.data.token);
				}
				return response;
			});
	}

	logout() {
		localStorage.removeItem('jwt');
	}
}

export default new AuthenticationService();
