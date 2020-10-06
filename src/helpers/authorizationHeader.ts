// @ts-ignore
import store from '../store';

export function authorizationHeader() {
	const token = store.getters['user/getToken'];
	if (token === null) {
		return {};
	}
	return {'Authorization': 'Bearer ' + token};
}
