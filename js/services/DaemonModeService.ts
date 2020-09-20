import store from '../store';

/**
 * IQRF Gateway Daemon operational mode service
 */
class DaemonModeService {

	/**
	 * Retrieve the current operational mode
	 */
	get(): Promise<any> {
		return this.set('');
	}

	/**
	 * Sets a new mode operational mode
	 * @param newMode New operational mode
	 */
	set(newMode: string): Promise<any> {
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
