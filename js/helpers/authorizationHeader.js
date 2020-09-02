export function authorizationHeader() {
	let json = localStorage.getItem('user');
	if (json) {
		let user = JSON.parse(json);
		return {'Authorization': 'Bearer ' + user.token};
	}
	return {};
}
