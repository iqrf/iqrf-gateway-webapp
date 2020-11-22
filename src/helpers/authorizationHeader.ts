import store from '../store';

/**
 * Creates authorization header object for REST API requests
 */
export function authorizationHeader(): {'Authorization'?: string} {
	const token = store.getters['user/getToken'];
	if (token === null) {
		return {};
	}
	return {'Authorization': 'Bearer ' + token};
}
