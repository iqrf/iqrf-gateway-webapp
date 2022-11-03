/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
import store from '@/store';
import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '@/helpers/authorizationHeader';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import {ISchedulerRecord} from '@/interfaces/DaemonApi/Scheduler';

/**
 * Scheduler service
 */
class SchedulerService {
	/**
	 * Adds a new task via the Daemon API
	 * @param {ISchedulerRecord} record Scheduler record
	 * @param {DaemonMessageOptions} options WebSocket request options
	 */
	addTask(record: ISchedulerRecord, options: DaemonMessageOptions): Promise<string> {
		delete record.newTaskId;
		options.request = {
			'mType': 'mngScheduler_AddTask',
			'data': {
				'req': record,
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Adds a new task via the REST API
	 * @param {ISchedulerRecord} record Scheduler record
	 */
	addTaskREST(record: ISchedulerRecord): Promise<AxiosResponse> {
		delete record.newTaskId;
		return axios.post('scheduler', record, {headers: authorizationHeader()});
	}

	/**
	 * Edits a task via the Daemon API
	 * @param {ISchedulerRecord} record Scheduler record
	 * @param {DaemonMessageOptions} options WebSocket request options
	 */
	editTask(record: ISchedulerRecord, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'mngScheduler_EditTask',
			'data': {
				'req': record,
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest',  options);
	}

	/**
	 * Edits an existing task via the REST API
	 * @param {ISchedulerRecord} record Scheduler record
	 */
	editTaskREST(record: ISchedulerRecord): Promise<AxiosResponse> {
		const oldTaskId = record.taskId;
		record.taskId = (record.newTaskId as string);
		delete record.newTaskId;
		return axios.put('scheduler/' + oldTaskId, record, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves scheduler tasks via the Daemon API
	 * @param {DaemonMessageOptions} options WebSocket request options
	 */
	listTasks(details: boolean, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'mngScheduler_List',
			'data': {
				'req': {
					'clientId': 'SchedulerMessaging',
					'details': details,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Retrieves scheduler tasks via the REST API
	 */
	listTasksREST(): Promise<AxiosResponse> {
		return axios.get('scheduler', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves task specified by ID via the Daemon API
	 * @param {string} taskId scheduler task ID
	 * @param {DaemonMessageOptions} options Daemon request options
	 */
	getTask(taskId: string, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'mngScheduler_GetTask',
			'data': {
				'req': {
					'clientId': 'SchedulerMessaging',
					'taskId': taskId,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Retrieves task specified by ID via the REST API
	 * @param {string} taskId scheduler task ID
	 */
	getTaskREST(taskId: string): Promise<AxiosResponse> {
		return axios.get('scheduler/' + taskId, {headers: authorizationHeader()});
	}

	/**
	 * Removes a task specified by ID via the Daemon API
	 * @param {string} taskId scheduler task ID
	 * @param {DaemonMessageOptions} options Daemon request options
	 */
	removeTask(taskId: string, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'mngScheduler_RemoveTask',
			'data': {
				'req': {
					'clientId': 'SchedulerMessaging',
					'taskId': taskId,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Removes a task specified by ID via the REST API
	 * @param {string} taskId scheduler ID
	 */
	removeTaskREST(taskId: string): Promise<AxiosResponse> {
		return axios.delete('/scheduler/' + taskId, {headers: authorizationHeader()});
	}

	/**
	 * Removes all tasks via the Daemon API
	 * @param {DaemonMessageOptions} options Websocket request options
	 */
	removeAll(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'mngScheduler_RemoveAll',
			'data': {
				'req': {
					'clientId': 'SchedulerMessaging',
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Removes all tasks via the REST API
	 */
	removeAllRest(): Promise<AxiosResponse> {
		return axios.delete('/scheduler', {headers: authorizationHeader()});
	}

	/**
	 * Schedules task
	 * @param {string} taskId Task ID
	 * @param {DaemonMessageOptions} options Websocket request options
	 */
	startTask(taskId: string, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'mngScheduler_StartTask',
			'data': {
				'req': {
					'clientId': 'SchedulerMessaging',
					'taskId': taskId,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Unschedules task
	 * @param {string} taskId Task ID
	 * @param {DaemonMessageOptions} options Websocket request options
	 */
	stopTask(taskId: string, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'mngScheduler_StopTask',
			'data': {
				'req': {
					'clientId': 'SchedulerMessaging',
					'taskId': taskId,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Exports scheduler configuration
	 */
	exportConfig(): Promise<AxiosResponse> {
		return axios.get('scheduler/export', {headers: authorizationHeader(), responseType: 'arraybuffer'});
	}

	/**
	 * Import scheduler configuration
	 * @param {File} config scheduler configuration
	 */
	importConfig(config: File): Promise<AxiosResponse> {
		const headers = {
			...authorizationHeader(),
			'Content-Type': config.type
		};
		return axios.post('scheduler/import', config, {headers: headers});
	}
}

export default new SchedulerService();
