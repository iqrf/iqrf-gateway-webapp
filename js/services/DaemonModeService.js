import store from '../store';

class DaemonModeService {

	get() {
		return this.set('');
	}

	set(newMode) {
		return store.dispatch('sendRequest', {
			'mType': 'mngDaemon_Mode',
			'data': {
				'msgId': 'mngDaemon_Mode',
				'req': {
					'operMode': newMode,
				},
				'returnVerbose': true,
			},
		});
	}
}

export default new DaemonModeService();
