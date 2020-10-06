import store from '../store';

/**
 * Version service
 */
class VersionService {

	getVersion() {
		return store.dispatch('sendRequest', {
			'mType': 'mngDaemon_Version',
			'data': {
				'returnVerbose': true,
			},
		});
	}
}

export default new VersionService();
