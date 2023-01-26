<!--
Copyright 2017-2022 IQRF Tech s.r.o.
Copyright 2019-2022 MICRORISC s.r.o.

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
					<CButton
						color='success'
						size='sm'
						to='/config/daemon/scheduler/add'
					>
						<CIcon :content='icons.add' size='sm' />
						{{ $t('table.actions.add') }}
					</CButton> <CButton
						color='primary'
						size='sm'
						@click='showImportModal = true'
					>
						<CIcon :content='icons.import' size='sm' />
						{{ $t('forms.import') }}
					</CButton> <CButton
						color='secondary'
						size='sm'
						@click='exportScheduler'
					>
						<CIcon :content='icons.export' size='sm' />
						{{ $t('forms.export') }}
					</CButton> <CButton
						color='danger'
						size='sm'
						@click='showDeleteAllModal = true'
					>
						<CIcon :content='icons.remove' size='sm' />
						{{ $t('table.actions.deleteAll') }}
					</CButton>
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
								color='info'
								size='sm'
								:to='"/config/daemon/scheduler/edit/" + (retrieved === "daemon" ? item.taskId : item.id)'
							>
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								color='danger'
								size='sm'
								@click='deleteTask = retrieved === "daemon" ? item.taskId : item.id'
							>
								<CIcon :content='icons.remove' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			:show.sync='showImportModal'
			color='primary'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.daemon.scheduler.import.title') }}
				</h5>
			</template>
			<CForm>
				<div class='form-group'>
					<CInputFile
						ref='schedulerImport'
						accept='application/json,.zip'
						:label='$t("config.daemon.scheduler.import.file")'
						@input='fileImportEmpty'
						@click='fileImportEmpty'
					/>
				</div>
			</CForm>
			<template #footer>
				<CButton
					color='primary'
					:disabled='importEmpty'
					@click='importScheduler'
				>
					{{ $t('forms.import') }}
				</CButton> <CButton
					color='secondary'
					@click='showImportModal = false'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
		<CModal
			color='danger'
			:show='deleteTask !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.daemon.scheduler.modal.title') }}
				</h5>
			</template>
			{{ $t('config.daemon.scheduler.modal.deletePrompt', {task: deleteTask}) }}
			<template #footer>
				<CButton
					color='danger'
					@click='removeTask'
				>
					{{ $t('forms.delete') }}
				</CButton> <CButton
					color='secondary'
					@click='deleteTask = null'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
		<CModal
			color='danger'
			:show.sync='showDeleteAllModal'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.daemon.scheduler.modal.title') }}
				</h5>
			</template>
			{{ $t('config.daemon.scheduler.modal.deleteAllPrompt') }}
			<template #footer>
				<CButton
					color='danger'
					@click='removeAllTasks'
				>
					{{ $t('table.actions.deleteAll') }}
				</CButton> <CButton
					color='secondary'
					@click='showDeleteAllModal = false'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CIcon, CInputFile, CModal} from '@coreui/vue/src';
import {cilPencil, cilPlus, cilTrash, cilArrowTop, cilArrowBottom} from '@coreui/icons';

import {DateTime, Duration} from 'luxon';
import {daemonErrorToast, extendedErrorToast} from '@/helpers/errorToast';
import {fileDownloader} from '@/helpers/fileDownloader';

import ServiceService from '@/services/ServiceService';
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
	},
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
	 * @var {number|null} deleteTask Id of scheduler task used in remove modal
	 */
	private deleteTask: number|null = null;

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
	 * @constant {Record<string, Array<string>>} icons Dictionary of CoreUI Icons
	 */
	private icons: Record<string, Array<string>> = {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
		import: cilArrowTop,
		export: cilArrowBottom,
	};

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
	private tasks: Array<ITaskRest> = [];

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * @var {boolean} showDeleteAllModal Controls delete all modal rendering
	 */
	private showDeleteAllModal = false;

	/**
	 * @var {boolean} showImportModal Controls import modal rendering
	 */
	private showImportModal = false;

	/**
	 * @var {boolean} importEmpty Indicates whether file input is empty or not
	 */
	private importEmpty = true;

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
					if (this.taskIds.length !== 0) {
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
				} else if (mutation.payload.mType === 'mngScheduler_RemoveTask') {
					this.handleRemoveTask(mutation.payload.data);
				} else if (mutation.payload.mType === 'mngScheduler_RemoveAll') {
					this.handleRemoveAll(mutation.payload.data);
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
	 * Removes a scheduler task
	 */
	private removeTask(): void {
		if (this.deleteTask === null) {
			return;
		}
		const task = this.deleteTask;
		this.deleteTask = null;
		if (this.$store.getters['daemonClient/isConnected']) {
			this.$store.dispatch('spinner/show', 30000);
			SchedulerService.removeTask(task, new DaemonMessageOptions(null, 30000, 'config.daemon.scheduler.messages.deleteFail'))
				.then((msgId: string) => this.storeId(msgId));
		} else {
			this.$store.commit('spinner/SHOW');
			SchedulerService.removeTaskREST(task)
				.then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('config.daemon.scheduler.messages.deleteSuccess').toString()
					);
					this.fetchTasks = true;
					this.getTasks();
				})
				.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.scheduler.messages.deleteFailedRest', {task: task}));
		}
	}

	/**
	 * Handles Daemon API RemoveTask response
	 * @param response Daemon API response
	 */
	private handleRemoveTask(response): void {
		if (response.status === 0) {
			this.$toast.success(
				this.$t('config.daemon.scheduler.messages.deleteSuccess').toString()
			);
			this.fetchTasks = true;
			this.getTasks();
		} else {
			this.$toast.error(
				this.$t('config.daemon.scheduler.messages.deleteFail').toString()
			);
		}
	}

	/**
	 * Removes all scheduler tasks
	 */
	private removeAllTasks(): void {
		this.showDeleteAllModal = false;
		if (this.$store.getters['daemonClient/isConnected']) {
			this.$store.dispatch('spinner/show', 30000);
			SchedulerService.removeAll(new DaemonMessageOptions(null, 30000, 'config.daemon.scheduler.messages.deleteAllFailed'))
				.then((msgId: string) => this.storeId(msgId));
		} else {
			this.$store.commit('spinner/SHOW');
			SchedulerService.removeAllRest()
				.then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('config.daemon.scheduler.messages.deleteAllSuccess').toString()
					);
					this.tasks = [];
				})
				.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.scheduler.messages.deleteAllFailedRest'));
		}
	}

	/**
	 * Handles Daemon API RemoveAll response
	 * @param response Daemon API response
	 */
	private handleRemoveAll(response): void {
		this.$store.dispatch('spinner/hide');
		if (response.status === 0) {
			this.$toast.success(
				this.$t('config.daemon.scheduler.messages.deleteAllSuccess').toString()
			);
			this.tasks = [];
		} else {
			this.$toast.error(
				this.$t('config.daemon.scheduler.messages.deleteAllFailed').toString()
			);
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
	 * Extracts files from import modal file input
	 * @returns {FileList} List of uploaded files
	 */
	private getFile(): FileList {
		const input = ((this.$refs.schedulerImport as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

	/**
	 * Checks if file input is empty
	 */
	private fileImportEmpty(): void {
		this.importEmpty = this.getFile().length === 0;
	}

	/**
	 * Imports scheduler tasks from zip file
	 */
	private importScheduler(): void {
		this.showImportModal = false;
		this.$store.commit('spinner/SHOW');
		const files = this.getFile();
		SchedulerService.importConfig(files[0])
			.then(() => {
				this.$toast.success(
					this.$t('config.daemon.scheduler.messages.importSuccess').toString()
				);
				ServiceService.restart('iqrf-gateway-daemon')
					.then(() => {
						this.$store.commit('spinner/HIDE');
						this.$toast.info(
							this.$t('service.iqrf-gateway-daemon.messages.restart')
								.toString()
						);
						this.fetchTasks = true;
						this.getTasks();
					})
					.catch((error: AxiosError) => daemonErrorToast(error, 'service.messages.restartFailed'));
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.scheduler.messages.importFailed'));
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
	 * @param {TaskTimeSpec} item Scheduler task time specification
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

<style scoped>
.card-header {
	padding-bottom: 0;
}

</style>
