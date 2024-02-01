import { type SleepParams } from '../embed';

/**
 * Standard Sensor read sensors parameters interface
 */
export interface ReadSensorsParams {
	/// Sensors to read data from
	sensorIndexes?: null | number | number[];
	/// Optional data to write
	writtenData?: number[][];
}

/**
 * Standard Sensor read sensors frc parameters interface
 */
export interface ReadSensorsFrcParams {
	/// Returns extended / verbose format
	extFormat?: boolean;
	/// Sensor FRC command
	frcCommand: number;
	/// Get extra FRC data that did not fit in response
	getExtraResult?: boolean;
	/// Selected nodes
	selectedNodes?: number[];
	/// Sensor index to read
	sensorIndex: number;
	/// Sensor type to read
	sensorType: number;
	/// Sleep after FRC concluded
	sleepAfterFrc?: SleepParams;
}
