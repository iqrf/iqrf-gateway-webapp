import axios from 'axios';
import store from '../store';

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
				store.commit('user/SIGN_IN', apiResponse.data);
				localStorage.setItem('user', JSON.stringify(apiResponse.data));
				return responses;
			});
	}

	logout() {
		store.commit('user/SIGN_OUT');
		localStorage.removeItem('user');
	}
}

export default new AuthenticationService();
