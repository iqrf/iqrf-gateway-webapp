import store from '../store';

export enum DaemonMode {
	getMode = '',
	forwarding = 'forwarding',
	operational = 'operational',
	service = 'service',
	unknown = 'unknown',
}

/**
 * IQRF Gateway Daemon operational mode service
 */
class DaemonModeService {

	/**
	 * Retrieve the current operational mode
	 */
	get(): Promise<any> {
		return this.set(DaemonMode.getMode);
	}

	/**
	 * Sets a new mode operational mode
	 * @param newMode New operational mode
	 */
	set(newMode: DaemonMode): Promise<any> {
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

	/**
	 * Parses Daemon mode response
	 * @param response Response from IQRF Gateway Daemon
	 */
	parse(response: any): DaemonMode {
		try {
			return response.data.rsp.operMode as DaemonMode;
		} catch (e) {
			return DaemonMode.unknown;
		}
	}
}

export default new DaemonModeService();
