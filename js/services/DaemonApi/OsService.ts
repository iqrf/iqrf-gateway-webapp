import store from '../../store';

/**
 * OS service
 */
class OsService {

	/**
	 * Sends OS Read request
	 * @param address Address
	 */
	sendRead(address: number): Promise<any> {
		return store.dispatch('sendRequest', {
			mType: 'iqrfEmbedOs_Read',
			data: {
				req: {
					nAdr: address,
					param: {},
				},
			},
		});
	}

}

export default new OsService();
