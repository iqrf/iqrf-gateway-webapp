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
	messagingList: string|MessagingInstance[];
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
