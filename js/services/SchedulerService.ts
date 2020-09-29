import store from '../store';
import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

class SchedulerService {
	addTask(taskId: number, clientId: number, task: any, timeSpec: object) {
		const tasks = JSON.parse(JSON.stringify(task));
		tasks.forEach((item: any) => {
			item.message = JSON.parse(item.message);
		});
		return store.dispatch('sendRequest', {
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
		});
	}

	/**
	 * Retrieve scheduler tasks
	 */
	listTasks() {
		return store.dispatch('sendRequest', {
			'mType': 'mngScheduler_List',
			'data': {
				'req': {
					'clientId': 'SchedulerMessaging',
				},
				'returnVerbose': true,
			},
		});
	}

	/**
	 * Retrieves task specified by ID
	 * @param taskId task ID
	 */
	getTask(taskId: number) {
		return store.dispatch('sendRequest', {
			'mType': 'mngScheduler_GetTask',
			'data': {
				'req': {
					'clientId': 'SchedulerMessaging',
					'taskId': taskId,
				},
				'returnVerbose': true,
			},
		});
	}

	removeTask(taskId: number) {
		return store.dispatch('sendRequest', {
			'mType': 'mngScheduler_RemoveTask',
			'data': {
				'req': {
					'clientId': 'SchedulerMessaging',
					'taskId': taskId,
				},
				'returnVerbose': true,
			},
		});
	}
	
	/**
	 * Exports scheduler configuration
	 */
	exportConfig(): Promise<AxiosResponse> {
		return axios.get('scheduler/export', {headers: authorizationHeader(), responseType: 'arraybuffer'});
	}
	
	/**
	 * Import scheduler configuration
	 * @param config scheduler configuration
	 */
	importConfig(config: any): Promise<AxiosResponse> {
		return axios.post('scheduler/import', config, {headers: authorizationHeader()});
	}
}

export default new SchedulerService();