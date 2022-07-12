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
		<v-card>
			<v-card-text>
				<v-data-table
					:headers='headers'
					:items='tasks'
					:no-data-text='$t("table.messages.noRecords")'
				>
					<template #top>
						<v-toolbar dense flat>
							<v-spacer />
							<v-btn
								color='success'
								small
								to='/config/daemon/scheduler/add'
							>
								<v-icon small>
									mdi-plus
								</v-icon>
								{{ $t('table.actions.add') }}
							</v-btn>
							<v-dialog
								v-model='showImportModal'
								width='50%'
								persistent
								no-click-animation
							>
								<template #activator='{on, attrs}'>
									<v-btn
										color='primary'
										small
										v-bind='attrs'
										v-on='on'
										@click='showImportModal = true'
									>
										<v-icon small>
											mdi-import
										</v-icon>
										{{ $t('forms.import') }}
									</v-btn>
								</template>
								<v-card>
									<ValidationObserver v-slot='{invalid}'>
										<v-card-title class='text-h5 primary'>
											{{ $t('config.daemon.scheduler.import.title') }}
										</v-card-title>
										<v-card-text>
											<v-form>
												<ValidationProvider
													v-slot='{errors, valid}'
													rules='required|taskFile'
													:custom-messages='{
														required: $t("config.daemon.scheduler.import.errors.file"),
														taskFile: $t("config.daemon.scheduler.import.errors.invalidFile"),
													}'
												>
													<v-file-input
														v-model='file'
														accept='application/json,.zip'
														:label='$t("config.daemon.scheduler.import.file")'
														:error-messages='errors'
														:success='valid'
														required
													/>
												</ValidationProvider>
											</v-form>
										</v-card-text>
										<v-card-actions>
											<v-spacer />
											<v-btn
												color='primary'
												:disabled='invalid'
												@click='importScheduler'
											>
												{{ $t('forms.import') }}
											</v-btn> <v-btn
												color='secondary'
												@click='showImportModal = false; file = null;'
											>
												{{ $t('forms.cancel') }}
											</v-btn>
										</v-card-actions>
									</ValidationObserver>
								</v-card>
							</v-dialog>
							<v-btn
								color='secondary'
								small
								@click='exportScheduler'
							>
								<v-icon small>
									mdi-export
								</v-icon>
								{{ $t('forms.export') }}
							</v-btn>
							<v-dialog
								v-model='showDeleteAllModal'
								width='50%'
								persistent
								no-click-animation
							>
								<template #activator='{on, attrs}'>
									<v-btn
										color='error'
										small
										v-bind='attrs'
										v-on='on'
										@click='showDeleteAllModal = true'
									>
										<v-icon small>
											mdi-delete
										</v-icon>
										{{ $t('table.actions.deleteAll') }}
									</v-btn>
								</template>
								<v-card>
									<v-card-title class='text-h5 error'>
										{{ $t('config.daemon.scheduler.modal.title') }}
									</v-card-title>
									<v-card-text>
										{{ $t('config.daemon.scheduler.modal.deleteAllPrompt') }}
									</v-card-text>
									<v-card-actions>
										<v-spacer />
										<v-btn
											color='error'
											@click='removeAllTasks'
										>
											{{ $t('table.actions.deleteAll') }}
										</v-btn> <v-btn
											color='secondary'
											@click='showDeleteAllModal = false'
										>
											{{ $t('forms.cancel') }}
										</v-btn>
									</v-card-actions>
								</v-card>
							</v-dialog>
						</v-toolbar>
					</template>
					<template v-if='retrieved === "rest"' #[`item.taskId`]='{item}'>
						{{ item.id }}
					</template>
					<template v-if='retrieved === "rest"' #[`item.clientId`]='{item}'>
						{{ item.service }}
					</template>
					<template #[`item.timeSpec`]='{item}'>
						{{ timeString(item.timeSpec) }}
					</template>
					<template #[`item.task`]='{item}'>
						<td v-if='retrieved === "daemon"'>
							{{ displayMTypes(item.task) }}
						</td>
						<td v-else>
							{{ displayMTypes(item.mTypes) }}
						</td>
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							color='info'
							small
							:to='"/config/daemon/scheduler/edit/" + (retrieved === "daemon" ? item.taskId : item.id)'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn>
						<v-dialog
							v-model='deleteDialog'
							width='50%'
							persistent
							no-click-animation
						>
							<template #activator='{on, attrs}'>
								<v-btn
									color='error'
									small
									v-bind='attrs'
									v-on='on'
									@click='deleteTask = retrieved === "daemon" ? item.taskId : item.id'
								>
									<v-icon small>
										mdi-delete
									</v-icon>
									{{ $t('table.actions.delete') }}
								</v-btn>
							</template>
							<v-card>
								<v-card-title class='text-h5 error'>
									{{ $t('config.daemon.scheduler.modal.title') }}
								</v-card-title>
								<v-card-text v-if='deleteTask !== null'>
									{{ $t('config.daemon.scheduler.modal.deletePrompt', {task: deleteTask}) }}
								</v-card-text>
								<v-card-actions>
									<v-spacer />
									<v-btn
										color='error'
										@click='removeTask'
									>
										{{ $t('forms.delete') }}
									</v-btn> <v-btn
										color='secondary'
										@click='deleteTask = null'
									>
										{{ $t('forms.cancel') }}
									</v-btn>
								</v-card-actions>
							</v-card>
						</v-dialog>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {DateTime, Duration} from 'luxon';
import {required} from 'vee-validate/dist/rules';
import {daemonErrorToast, extendedErrorToast} from '@/helpers/errorToast';
import {fileDownloader} from '@/helpers/fileDownloader';

import ServiceService from '@/services/ServiceService';
import SchedulerService from '@/services/SchedulerService';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {ITaskRest, ITaskTimeSpec} from '@/interfaces/scheduler';
import {MutationPayload} from 'vuex';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
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
	 * @constant {Array<DataTableHeader>} headers Vuetify data table headers
	 */
	private headers: Array<DataTableHeader> = [
		{
			value: 'taskId',
			text: this.$t('config.daemon.scheduler.form.task.taskId').toString(),
		},
		{
			value: 'timeSpec',
			text: this.$t('config.daemon.scheduler.table.time').toString(),
			filterable: false,
			sortable: false,
		},
		{
			value: 'clientId',
			text: this.$t('config.daemon.scheduler.table.service').toString(),
		},
		{
			value: 'task',
			text: this.$t('config.daemon.scheduler.table.mType').toString(),
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			filterable: false,
			sortable: false,
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
	 * @var {boolean} fetchTasks Indicates whether tasks should be fetched
	 */
	private fetchTasks = true;

	/**
	 * @var {File|null} file Task file to import
	 */
	private file: File|null = null;

	/**
	 * @var {boolean} deleteDialog Delete dialog visibility
	 */
	get deleteDialog(): boolean {
		return this.deleteTask !== null;
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
		extend('taskFile', (file: File|null) => {
			if (!file) {
				return false;
			}
			if (!['application/json', 'application/zip'].includes(file.type)) {
				return false;
			}
			return true;
		});
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
	 * Imports scheduler tasks from zip file
	 */
	private importScheduler(): void {
		if (!this.file) {
			return;
		}
		this.showImportModal = false;
		this.$store.commit('spinner/SHOW');
		SchedulerService.importConfig(this.file)
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

<style scoped>
.card-header {
	padding-bottom: 0;
}

</style>
