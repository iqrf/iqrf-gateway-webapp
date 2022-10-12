<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<div>
		<h1>
			{{ $t('config.daemon.scheduler.title') }}
		</h1>
		<CCard>
			<CCardHeader class='border-0'>
				<div class='float-right'>
					<CButtonToolbar>
						<CButton
							class='mr-1'
							color='success'
							size='sm'
							to='/config/daemon/scheduler/add'
						>
							<CIcon :content='cilPlus' size='sm' />
							{{ $t('table.actions.add') }}
						</CButton>
						<TaskImportModal
							class='mr-1'
							@imported='refreshTasks'
						/>
						<CButton
							class='mr-1'
							color='secondary'
							size='sm'
							@click='exportScheduler'
						>
							<CIcon
								:content='cilArrowBottom'
								size='sm'
							/>
							{{ $t('forms.export') }}
						</CButton>
						<TasksDeleteModal
							@deleted='refreshTasks'
						/>
					</CButtonToolbar>
				</div>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:fields='fields'
					:items='tasks'
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
					:striped='true'
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template v-if='retrieved === "rest"' #taskId='{item}'>
						<td>
							{{ item.id }}
						</td>
					</template>
					<template v-if='retrieved === "rest"' #clientId='{item}'>
						<td>
							{{ item.service }}
						</td>
					</template>
					<template #timeSpec='{item}'>
						<td>
							{{ timeString(item.timeSpec) }}
						</td>
					</template>
					<template #task='{item}'>
						<td v-if='retrieved === "daemon"'>
							{{ displayMTypes(item.task) }}
						</td>
						<td v-else>
							{{ displayMTypes(item.mTypes) }}
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								class='mr-1'
								color='info'
								size='sm'
								:to='"/config/daemon/scheduler/edit/" + (retrieved === "daemon" ? item.taskId : item.id)'
							>
								<CIcon :content='cilPencil' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<TaskDeleteModal
								:task-id='retrieved === "daemon" ? item.taskId : item.id'
								@deleted='refreshTasks'
							/>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CIcon, CInputFile, CModal} from '@coreui/vue/src';
import TaskDeleteModal from '@/components/Config/Scheduler/TaskDeleteModal.vue';
import TasksDeleteModal from '@/components/Config/Scheduler/TasksDeleteModal.vue';
import TaskImportModal from '@/components/Config/Scheduler/TaskImportModal.vue';

import {cilArrowBottom, cilPencil, cilPlus} from '@coreui/icons';
import {DateTime, Duration} from 'luxon';
import {extendedErrorToast} from '@/helpers/errorToast';
import {fileDownloader} from '@/helpers/fileDownloader';

import SchedulerService from '@/services/SchedulerService';

import {AxiosError, AxiosResponse} from 'axios';
import {IField} from '@/interfaces/coreui';
import {ITaskRest, ITaskTimeSpec} from '@/interfaces/scheduler';
import {MutationPayload} from 'vuex';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CIcon,
		CInputFile,
		CModal,
		TaskDeleteModal,
		TasksDeleteModal,
		TaskImportModal,
	},
	data: () => ({
		cilArrowBottom,
		cilPencil,
		cilPlus,
	}),
	metaInfo: {
		title: 'config.daemon.scheduler.title',
	}
})

/**
 * List of Daemon scheduler tasks
 */
export default class SchedulerList extends Vue {

	/**
	 * @constant {Diction<string|boolean>} dateFormat Date formatting options
	 */
	private dateFormat: Record<string, string|boolean> = {
		year: 'numeric',
		month: 'short',
		day: 'numeric',
		hour12: false,
		hour: 'numeric',
		minute: 'numeric',
		second: 'numeric',
	};

	/**
	 * @constant {Array<IField>} fields Array of CoreUI data table columns
	 */
	private fields: Array<IField> = [
		{
			key: 'taskId',
			label: this.$t('config.daemon.scheduler.form.task.taskId'),
		},
		{
			key: 'timeSpec',
			label: this.$t('config.daemon.scheduler.table.time'),
			filter: false,
			sorter: false,
		},
		{
			key: 'clientId',
			label: this.$t('config.daemon.scheduler.table.service'),
		},
		{
			key: 'task',
			label: this.$t('config.daemon.scheduler.table.mType'),
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			filter: false,
			sorter: false,
		},
	];

	/**
	 * @var {Array<string>} msgIds Array of message ids used for response handling
	 */
	private msgIds: Array<string> = [];

	/**
	 * @var {string|null} retrieved Specifies where the message was retrieved from
	 */
	private retrieved: string|null = null;

	/**
	 * @var {Array<number>} taskIds Array of scheduler task ids
	 */
	private taskIds: Array<number> = [];

	/**
	 * @var {Array<Task>} tasks Array of scheduler tasks
	 */
	private tasks: Array<ITaskRest>|null = null;

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * @var {boolean} fetchTasks Indicates whether tasks should be fetched
	 */
	private fetchTasks = true;

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.$store.commit('spinner/SHOW');
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONOPEN') { // websocket connection with daemon established
				this.getTasks();
			} else if (mutation.type === 'daemonClient/SOCKET_ONCLOSE' ||
				mutation.type === 'daemonClient/SOCKET_ONERROR') { // websocket connection with daemon terminated, REST fallback
			} else if (mutation.type === 'daemonClient/SOCKET_ONSEND') { // cleanup before tasks are retrieved
				if (mutation.payload.mType === 'mngScheduler_List') {
					if (this.taskIds.length === 0) {
						this.taskIds = [];
						this.tasks = [];
					}
				}
			} else if (mutation.type === 'daemonClient/SOCKET_ONMESSAGE') {
				if (!this.msgIds.includes(mutation.payload.data.msgId)) {
					return;
				}
				this.$store.dispatch('spinner/hide');
				this.$store.dispatch('daemonClient/removeMessage', mutation.payload.data.msgId);
				if (mutation.payload.mType === 'mngScheduler_List') {
					this.handleList(mutation.payload.data);
				} else if (mutation.payload.mType === 'mngScheduler_GetTask') {
					this.handleGetTask(mutation.payload.data);
				} else if (mutation.payload.mType === 'messageError') {
					this.handleMessageError(mutation.payload.data);
				}
			}
		});
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getTasks();
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.msgIds.forEach((item) => this.$store.dispatch('daemonClient/removeMessage', item));
		this.unsubscribe();
	}

	/**
	 * Queues task refresh
	 */
	private refreshTasks(): void {
		this.fetchTasks = true;
		this.getTasks();
	}

	/**
	 * Retrieves list of scheduler tasks
	 */
	private getTasks(): void {
		if (!this.fetchTasks) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		this.fetchTasks = false;
		setTimeout(() => {
			if (this.$store.getters['daemonClient/isConnected']) {
				this.$store.dispatch('spinner/show', 30000);
				SchedulerService.listTasks(new DaemonMessageOptions(null, 30000, 'config.daemon.scheduler.messages.listFailed'))
					.then((msgId: string) => this.storeId(msgId));
			} else {
				this.$store.commit('spinner/SHOW');
				SchedulerService.listTasksREST()
					.then((response: AxiosResponse) => {
						this.$store.commit('spinner/HIDE');
						this.tasks = response.data;
						this.retrieved = 'rest';
					})
					.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.scheduler.messages.listFailedRest'));
			}
		}, 1000);
	}

	/**
	 * Handles Daemon API List response
	 * @param response Daemon API response
	 */
	private handleList(response): void {
		if (response.status === 0) {
			this.taskIds = response.rsp.tasks;
			if (this.taskIds.length === 0) {
				this.tasks = [];
			}
			this.taskIds.forEach(item => {
				this.getTask(item);
			});
			this.retrieved = 'daemon';
		} else {
			this.$toast.error(
				this.$t('config.daemon.scheduler.messages.listFailed').toString()
			);
		}
	}

	/**
	 * Retrieves a scheduler task specified by task id
	 * @param {number} taskId Scheduler task id
	 */
	private getTask(taskId: number): void {
		SchedulerService.getTask(taskId, new DaemonMessageOptions(null, 30000))
			.then((msgId: string) => this.storeId(msgId));
	}

	/**
	 * Handles Daemon API GetTask response
	 * @param response Daemon API response
	 */
	private handleGetTask(response): void {
		if (response.status === 0) {
			if (this.tasks === null) {
				return;
			}
			this.tasks.push(response.rsp);
		}
	}

	/**
	 * Handles Daemon API messageError response
	 * @param response Daemon API response
	 */
	private handleMessageError(response): void {
		if (response.rsp.errorStr.includes('daemon overload')) {
			this.$toast.error(
				this.$t('iqrfnet.sendJson.messages.error.messageQueueFull').toString()
			);
		} else {
			this.$toast.error(
				this.$t('config.daemon.scheduler.messages.processError').toString()
			);
		}
	}

	/**
	 * Stores a daemon api message id for response handling
	 */
	private storeId(msgId: string): void {
		this.msgIds.push(msgId);
		setTimeout(() => {
			if (this.msgIds.includes(msgId)) {
				this.msgIds.splice(this.msgIds.indexOf(msgId), 1);
			}
		}, 30000);
	}

	/**
	 * Exports scheduler tasks
	 */
	private exportScheduler(): void {
		this.$store.commit('spinner/SHOW');
		SchedulerService.exportConfig()
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				const fileName = 'iqrf-gateway-scheduler_' + + new Date().toISOString();
				const file = fileDownloader(response, 'application/zip', fileName);
				file.click();
			})
			.catch((error: AxiosError) => {
				if (error.response === undefined || error.response.status !== 404) {
					extendedErrorToast(error, 'config.daemon.scheduler.messages.exportFailed');
				} else {
					this.$store.commit('spinner/HIDE');
					this.$toast.error(
						this.$t('config.daemon.scheduler.messages.exportNoTasks').toString()
					);
				}
			});
	}

	/**
	 * Creates a string of daemon api message types displayed in scheduler task table
	 * @param item Task message or task message object
	 * @returns {string} Task daemon api message types
	 */
	private displayMTypes(item): string {
		try {
			if (this.retrieved === 'rest') { // task retrieved from REST API, message types is array of strings or a string
				return Array.isArray(item) ? item.join(', ') : item;
			}
			if (Array.isArray(item)) { // task retrieved from daemon API and has multiple messages
				let message = '';
				item.forEach(item => {
					message += item.message.mType + ', ';
				});
				return message.slice(0, -2);
			} else { // task retrieved from daemon API and has only one message
				return item.message.mType;
			}
		} catch(err) {
			return '';
		}
	}

	/**
	 * Creates time string used in scheduler task data table from task time specification
	 * @param {ITaskTimeSpec} item Scheduler task time specification
	 * @returns {string} Human readable time message
	 */
	private timeString(item: ITaskTimeSpec): string|undefined {
		try {
			if (item.exactTime) {
				return 'oneshot (' + DateTime.fromISO(item.startTime).toLocaleString(this.dateFormat) + ')';
			}
			if (item.periodic) { // human readable periodic time specification conversion
				let message = 'every ';
				const duration = Duration.fromMillis(item.period * 1000);
				if (item.period >= 0 && item.period < 60) {
					message += duration.toFormat('s') + ' s';
				} else if (item.period < 3600) {
					message += duration.toFormat('m:ss') + ' min';
				} else {
					message += duration.toFormat('h:mm:ss') + ' h';
				}
				return message;
			}
			if (item.cronTime.length > 0) { // time specification in cron, conversion from array to string
				if (typeof item.cronTime === 'string') {
					return item.cronTime;
				}
				return item.cronTime.join(' ').trim();
			}
		} catch (err) {
			return '';
		}
	}
}
</script>
