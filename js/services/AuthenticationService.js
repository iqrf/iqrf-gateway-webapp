import axios from 'axios';

class AuthenticationService {
	apiLogin(username, password) {
		let data = {
			username: username,
			password: password
		};
		return axios.post('user/signIn', data);
	}

	netteLogin(username, password) {
		const data = new URLSearchParams();
		data.append('username', username);
		data.append('password', password);
		data.append('remember', 'on');
		data.append('send', 'Sign+in');
		data.append('_do', 'signInForm-submit');
		return axios.post('//' + window.location.host + '/sign/in', data);
	}

	login(username, password) {
		return Promise.all([
			this.apiLogin(username, password),
			this.netteLogin(username, password)
		])
			.then((responses) => {
				const apiResponse = responses[0];
				if (apiResponse.status === 200 && apiResponse.data.token) {
					localStorage.setItem('jwt', apiResponse.data.token);
				}
				return responses;
			});
	}

	logout() {
		localStorage.removeItem('jwt');
	}
}

export default new AuthenticationService();
