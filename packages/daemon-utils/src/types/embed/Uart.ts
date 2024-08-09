import { type UartBaudRates } from '../../enums/embed';

/**
 * Embedded OS Batch raw parameters interface
 */
export interface UartOpenParams {
	/// Baud rate
	baudRate: UartBaudRates;
}
