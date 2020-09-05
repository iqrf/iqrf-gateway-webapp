import store from '../store';

/**
 * IQRF Gateway Daemon operational mode service
 */
class DaemonModeService {

	/**
	 * Retrieve the current operational mode
	 * @returns {Promise<any>}
	 */
	get() {
		return this.set('');
	}

	/**
	 * Sets a new mode operational mode
	 * @param newMode New operational mode
	 * @returns {Promise<any>}
	 */
	set(newMode) {
		return store.dispatch('sendRequest', {
			'mType': 'mngDaemon_Mode',
			'data': {
				'req': {
					'operMode': newMode,
				},
				'returnVerbose': true,
			},
		});
	}
}

export default new DaemonModeService();
