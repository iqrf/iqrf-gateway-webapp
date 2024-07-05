import { type FrcCommand } from '../../enums/embed';
import { type IqmeshTestRfMeasurementTime } from '../../enums/iqmesh';

/**
 * IQMESH Frc command response time test parameters interface
 */
export interface IqmeshFrcResponseTimeParams {
	/// FRC command to test
	command: FrcCommand;
}

/**
 * IQMESH Test RF signal parameters interface
 */
export interface IqmeshTestRfSignalParams {
	/// Address of device
	deviceAddr: number;
	/// Measurement time
	measurementTime: IqmeshTestRfMeasurementTime;
	/// RF channel to test
	rfChannel: number;
	/// RX filter
	rxFilter: number;
}
