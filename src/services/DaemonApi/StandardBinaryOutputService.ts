import store from '../../store';
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';

export class StandardBinaryOutput {

	/**
	 * Index of the binary output
	 */
	public index: number;

	/**
	 * State of the binary output
	 */
	public state: boolean;

	/**
	 * Constructor
	 * @param index Index of the binary output
	 * @param state State of the binary output
	 */
	public constructor(index: number, state: boolean) {
		this.index = index;
		this.state = state;
	}
}

/**
 * IQRF Standard binary output service
 */
class StandardBinaryOutputService {

	/**
	 * Performs Binary Output enumeration on device specified by address.
	 * @param address Node address
	 * @param options WebSocket request options
	 */
	enumerate(address: number, options: WebSocketOptions): Promise<any> {
		options.request = {
			'mType': 'iqrfBinaryoutput_Enumerate',
			'data': {
				'req': {
					'nAdr': address,
					'param': {},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Retrieves states of binary outputs.
	 * @param address Node address
	 * @param options WebSocket request options
	 */
	getOutputs(address: number, options: WebSocketOptions): Promise<any> {
		return this.setOutputs(address, [], options);
	}

	/**
	 * Sets binary outputs to a specified state.
	 * If no output settings are specified, only previous states of binary outputs are retrieved.
	 * @param address Node address
	 * @param outputs New output setting
	 * @param options WebSocket request options
	 */
	setOutputs(address: number, outputs: StandardBinaryOutput[] = [], options: WebSocketOptions): Promise<any> {
		options.request = {
			'mType': 'iqrfBinaryoutput_SetOutput',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'binOuts': outputs,
					},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('sendRequest', options);
	}
}

export default new StandardBinaryOutputService();
