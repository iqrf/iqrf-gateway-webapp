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
import {ITaskTimeSpec} from '@/interfaces/DaemonApi/Scheduler';

/**
 * Scheduler service
 */
class SchedulerService {
	/**
	 * Adds a new task via the Daemon API
	 * @param {number} taskId scheduler task ID
	 * @param {string} clientId client ID
	 * @param {any} task scheduler task
	 * @param {ITaskTimeSpec} timeSpec scheduler task time settings
	 * @param {DaemonMessageOptions} options WebSocket request options
	 */
	addTask(taskId: number, clientId: string, task: any, timeSpec: ITaskTimeSpec, options: DaemonMessageOptions): Promise<string> {
		const tasks = JSON.parse(JSON.stringify(task));
		tasks.forEach((item: any) => {
			item.message = JSON.parse(item.message);
		});
		options.request = {
			'mType': 'mngScheduler_AddTask',
			'data': {
				'req': {
					'clientId': clientId,
					'taskId': taskId,
					'task': tasks,
					'timeSpec': timeSpec,
					'persist': true,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Adds a new task via the REST API
	 * @param {number} taskId scheduler task ID
	 * @param {string} clientId client ID
	 * @param {any} task scheduler task
	 * @param {ITaskTimeSpec} timeSpec scheduler task time settings
	 */
	addTaskREST(taskId: number, clientId: string, task: any, timeSpec: ITaskTimeSpec): Promise<AxiosResponse> {
		const tasks = JSON.parse(JSON.stringify(task));
		tasks.forEach((item: any) => {
			item.message = JSON.parse(item.message);
		});
		const newTask = {
			'taskId': taskId,
			'clientId': clientId,
			'task': tasks,
			'timeSpec': timeSpec
		};
		return axios.post('scheduler', newTask, {headers: authorizationHeader()});
	}

	/**
	 * Edits an existing task via the REST API
	 * @param {number} oldTaskId existing task ID
	 * @param {number} taskId new task ID
	 * @param {string} clientId client ID
	 * @param {any} task scheduler task
	 * @param {ITaskTimeSpec} timeSpec scheduler task time settings
	 */
	editTaskREST(oldTaskId: number, taskId: number, clientId: string, task: any, timeSpec: ITaskTimeSpec): Promise<AxiosResponse> {
		const tasks = JSON.parse(JSON.stringify(task));
		tasks.forEach((item: any) => {
			item.message = JSON.parse(item.message);
		});
		const editTask = {
			'taskId': taskId,
			'clientId': clientId,
			'task': tasks,
			'timeSpec': timeSpec
		};
		return axios.put('scheduler/' + oldTaskId, editTask, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves scheduler tasks via the Daemon API
	 * @param {DaemonMessageOptions} options WebSocket request options
	 */
	listTasks(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'mngScheduler_List',
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
	 * Retrieves scheduler tasks via the REST API
	 */
	listTasksREST(): Promise<AxiosResponse> {
		return axios.get('scheduler', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves task specified by ID via the Daemon API
	 * @param {number} taskId scheduler task ID
	 * @param {DaemonMessageOptions} options Daemon request options
	 */
	getTask(taskId: number, options: DaemonMessageOptions): Promise<string> {
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
	 * @param {number} taskId scheduler task ID
	 */
	getTaskREST(taskId: number): Promise<AxiosResponse> {
		return axios.get('scheduler/' + taskId, {headers: authorizationHeader()});
	}

	/**
	 * Removes a task specified by ID via the Daemon API
	 * @param {number} taskId scheduler task ID
	 * @param {DaemonMessageOptions} options Daemon request options
	 */
	removeTask(taskId: number, options: DaemonMessageOptions): Promise<string> {
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
	 * @param {number} taskId scheduler ID
	 */
	removeTaskREST(taskId: number): Promise<AxiosResponse> {
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
