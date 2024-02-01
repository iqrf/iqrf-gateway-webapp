import { SchedulerMessages } from '../enums';
import { type SchedulerRecord } from '../types';
import { type DaemonMessageOptions } from '../utils';

/**
 * Scheduler API service
 */
export class SchedulerService {

	/// Client ID
	public static readonly ClientID = 'SchedulerMessaging';

	/**
	 * Add new scheduler task
	 * @param {SchedulerRecord} record Scheduler task
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static addTask(record: SchedulerRecord, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: SchedulerMessages.AddTask,
			data: {
				req: record,
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Edit existing scheduler task
	 * @param {SchedulerRecord} record Scheduler task
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static editTask(record: SchedulerRecord, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: SchedulerMessages.EditTask,
			data: {
				req: record,
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Fetches task by ID
	 * @param {string} taskId Task ID
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static getTask(taskId: string, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: SchedulerMessages.GetTask,
			data: {
				req: {
					clientId: SchedulerService.ClientID,
					taskId: taskId,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Fetches list of scheduler tasks
	 * @param {boolean} details Include full task details in response
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static listTasks(details: boolean, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: SchedulerMessages.ListTasks,
			data: {
				req: {
					clientId: SchedulerService.ClientID,
					details: details,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Removes all scheduler tasks
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static removeAllTasks(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: SchedulerMessages.RemoveAll,
			data: {
				req: {
					clientId: SchedulerService.ClientID,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Remove scheduler task by ID
	 * @param {string} taskId Task ID
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static removeTask(taskId: string, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: SchedulerMessages.RemoveTask,
			data: {
				req: {
					clientId: SchedulerService.ClientID,
					taskId: taskId,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Start task by ID
	 * @param {string} taskId Task ID
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static startTask(taskId: string, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: SchedulerMessages.StartTask,
			data: {
				req: {
					clientId: SchedulerService.ClientID,
					taskId: taskId,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Stop task by ID
	 * @param {string} taskId Task ID
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static stopTask(taskId: string, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: SchedulerMessages.StopTask,
			data: {
				req: {
					clientId: SchedulerService.ClientID,
					taskId: taskId,
				},
				returnVerbose: true,
			},
		};
		return options;
	}
}
