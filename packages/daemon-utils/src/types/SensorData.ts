import { type MessagingInstance } from './Messaging';

/**
 * SensorData configuration interface
 */
export interface SensorDataConfig {
	/// Report collected data asynchronously
	asyncReports: boolean;
	/// Run service worker on startup
	autoRun: boolean;
	/// List of messaging service instances to use for reporting
	messagingList: MessagingInstance[];
	/// Service worker period
	period: number;
	/// Worker retry period following failure
	retryPeriod: number;
}

/**
 * SensorData status interface
 */
export interface SensorDataStatus {
	/// Is data reading in progress?
	reading: boolean;
	/// Is worker running?
	running: boolean;
}

/**
 * SensorData asynchronous report interface
 */
export interface SensorDataReport {
	/// Data collecting in prgoress
	reading: boolean;
	/// Collected data
	devices?: SensorDataReportDevice[];
}

/**
 * SensorData asynchronous report device interface
 */
export interface SensorDataReportDevice {
	/// Device address
	address: number;
	/// Hardware profile ID
	hwpid: number;
	/// Module ID
	mid: number;
	/// RSSI
	rssi: number;
	/// Collected sensor data
	sensors: SensorDataReportSensor[];
}

/**
 * SensorData asynchronous report sensor interface
 */
export interface SensorDataReportSensor {
	/// Sensor index
	index: number;
	/// Sensor type
	type: number;
	/// Quantity name
	name: string;
	/// Quantity unit
	unit: string;
	/// Measured value
	value: string;
}
