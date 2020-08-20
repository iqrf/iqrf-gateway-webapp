export function authorizationHeader() {
	let jwt = localStorage.getItem('jwt');
	if (jwt) {
		return {'Authorization': 'Bearer ' + jwt};
	}
	return {};
}
