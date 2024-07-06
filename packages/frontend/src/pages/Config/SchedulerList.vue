<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		<h1>{{ $t('config.daemon.scheduler.title') }}</h1>
		<v-card>
			<v-card-text>
				<v-data-table
					:loading='loading'
					:headers='headers'
					:items='tasks'
					:no-data-text='$t("table.messages.noRecords")'
				>
					<template #top>
						<v-toolbar dense flat>
							<h5>{{ $t('config.daemon.scheduler.table.title') }}</h5>
							<v-spacer />
							<v-btn
								class='mr-1'
								color='success'
								small
								to='/config/daemon/scheduler/add'
							>
								<v-icon small>
									mdi-plus
								</v-icon>
								{{ $t('table.actions.add') }}
							</v-btn>
							<TaskImportModal
								@imported='getTasks'
							/>
							<v-btn
								class='mr-1'
								color='secondary'
								small
								@click='exportScheduler'
							>
								<v-icon small>
									mdi-export
								</v-icon>
								{{ $t('forms.export') }}
							</v-btn>
							<v-btn
								class='mr-1'
								color='primary'
								small
								@click='getTasks'
							>
								<v-icon small>
									mdi-refresh
								</v-icon>
								{{ $t('forms.refresh') }}
							</v-btn>
							<TasksDeleteModal
								@deleted='getTasks'
							/>
						</v-toolbar>
					</template>
					<template #[`item.active`]='{item}'>
						<v-icon
							v-if='item.active !== undefined'
							:color='item.active ? "success" : "error"'
						>
							{{ item.active ? 'mdi-close-circle-outline' : 'mdi-check-circle-outline' }}
						</v-icon>
						<span v-else>{{ $t('forms.notAvailable') }}</span>
					</template>
					<template #[`item.timeSpec`]='{item}'>
						{{ timeString(item.timeSpec) }}
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							v-if='item.active !== undefined'
							class='mr-1'
							:color='item.active ? "error" : "success"'
							small
							@click='item.active ? stopTask(item.taskId) : startTask(item.taskId)'
						>
							<v-icon small>
								{{ item.active ? 'mdi-close-circle-outline' : 'mdi-check-circle-outline' }}
							</v-icon>
							{{ $t(item.active ? 'forms.stop' : 'forms.start') }}
						</v-btn>
						<v-btn
							class='mr-1'
							color='info'
							small
							:to='"/config/daemon/scheduler/edit/" + item.taskId'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn>
						<v-btn
							color='error'
							small
							@click='recordDeleteModel = item.taskId'
						>
							<v-icon small>
								mdi-delete
							</v-icon>
							{{ $t('table.actions.delete') }}
						</v-btn>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
		<TaskDeleteModal
			v-model='recordDeleteModel'
			@deleted='getTasks'
		/>
	</div>
</template>

<script lang='ts'>
import {FileResponse} from '@iqrf/iqrf-gateway-webapp-client/types';
import {IqrfGatewayDaemonService} from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {FileDownloader} from '@iqrf/iqrf-gateway-webapp-client/utils';
import {AxiosError, AxiosResponse} from 'axios';
import {DateTime, Duration} from 'luxon';
import {Component, Vue} from 'vue-property-decorator';
import {DataTableHeader} from 'vuetify';
import {MutationPayload} from 'vuex';

import TaskDeleteModal from '@/components/Config/Scheduler/TaskDeleteModal.vue';
import TasksDeleteModal from '@/components/Config/Scheduler/TasksDeleteModal.vue';
import TaskImportModal from '@/components/Config/Scheduler/TaskImportModal.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import SchedulerRecord from '@/helpers/SchedulerRecord';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import SchedulerService from '@/services/SchedulerService';
import {ISchedulerRecord, ISchedulerRecordTimeSpec} from '@/interfaces/DaemonApi/Scheduler';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		TaskDeleteModal,
		TasksDeleteModal,
		TaskImportModal,
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
	 * @var {boolean} loading Indicates whether scheduler task data is loading
	 */
	private loading = false;

	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * @var {Array<ITask>} tasks Array of scheduler tasks
	 */
	private tasks: Array<ISchedulerRecord> = [];

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
	 * @var {boolean} lastFetchedRest Indicates whether last task list fetch was done via REST API
	 */
	private lastFetchedRest = false;

	/**
	 * @var {boolean} daemonWatched Indicates whether daemon reconnect is watched
	 */
	private daemonWatched = false;

	/**
	 * @var {ISchedulerRecord|null} recordDeleteModel Record to delete
	 */
	private recordDeleteModel: ISchedulerRecord|null = null;

	/**
	 * @constant {Array<DataTableHeader>} headers Data table headers
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'taskId',
			text: this.$t('config.daemon.scheduler.form.task.taskId').toString(),
		},
		{
			value: 'description',
			text: this.$t('config.daemon.scheduler.form.task.description').toString(),
			filterable: false,
			sortable: false,
		},
		{
			value: 'timeSpec',
			text: this.$t('config.daemon.scheduler.table.time').toString(),
			filterable: false,
			sortable: false,
		},
		{
			value: 'active',
			text: this.$t('config.daemon.scheduler.table.active').toString(),
			filterable: false,
			sortable: false,
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			filterable: false,
			sortable: false,
			align: 'end',
		},
	];

	/**
	 * Subscribe function callback
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Watch function callback
	 */
	private unwatch: CallableFunction = () => {return;};

	private service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONCLOSE') {
				if (!this.lastFetchedRest) {
					this.listRest();
				}
				if (!this.daemonWatched) {
					this.setWatch();
				}
			} else if (mutation.type === 'daemonClient/SOCKET_ONMESSAGE') {
				if (mutation.payload.data.msgId != this.msgId) {
					return;
				}
				this.loading = false;
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				if (mutation.payload.mType === 'mngScheduler_List') {
					this.handleList(mutation.payload.data);
				} else if (mutation.payload.mType === 'mngScheduler_StartTask') {
					this.handleStartTask(mutation.payload.data);
				} else if (mutation.payload.mType === 'mngScheduler_StopTask') {
					this.handleStopTask(mutation.payload.data);
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
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
		this.unwatch();
	}

	/**
	 * Sets daemon api task fetch
	 */
	private setWatch(): void {
		this.unwatch = this.$store.watch(
			(state, getter) => getter['daemonClient/isConnected'],
			(newVal, oldVal) => {
				if (!oldVal && newVal) {
					this.loading = true;
					setTimeout(() => {
						this.list();
						this.unwatch();
					}, 10000);
				}
			}
		);
		this.daemonWatched = true;
	}

	/**
	 * Retrieves list of scheduler tasks
	 */
	private getTasks(): void {
		this.loading = true;
		setTimeout(() => {
			if (this.$store.getters['daemonClient/isConnected']) {
				this.list();
			} else {
				this.listRest();
			}
		}, 1000);
	}

	/**
	 * Retrieves task list via Daemon API
	 */
	private list(): void {
		SchedulerService.listTasks(true, new DaemonMessageOptions(null, 30000, 'config.daemon.scheduler.messages.listFailed'))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Retrieves task list via REST API
	 */
	private listRest(): void {
		this.loading = true;
		this.lastFetchedRest = true;
		SchedulerService.listTasksREST()
			.then((response: AxiosResponse) => {
				this.tasks = response.data;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.scheduler.messages.listFailedRest');
				this.loading = false;
			});
	}

	/**
	 * Handles Daemon API List response
	 * @param response Daemon API response
	 */
	private handleList(response): void {
		if (response.status === 0) {
			this.tasks = response.rsp.tasks;
			this.lastFetchedRest = false;
			this.daemonWatched = false;
		} else {
			this.$toast.error(
				this.$t('config.daemon.scheduler.messages.listFailed').toString()
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
	 * Starts task
	 * @param {string} taskId Task ID
	 */
	private startTask(taskId: string): void {
		this.loading = true;
		SchedulerService.startTask(taskId, new DaemonMessageOptions(null, 10000, 'config.daemon.scheduler.messages.startTimeout'))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles StartTask Daemon API response
	 * @param response Daemon API response
	 */
	private handleStartTask(response): void {
		if (response.status === 0) {
			this.$toast.success(
				this.$t('config.daemon.scheduler.messages.startSuccess', {task: response.rsp.taskId}).toString()
			);
			this.getTasks();
		} else {
			this.$toast.error(
				this.$t('config.daemon.scheduler.messages.startFailed').toString()
			);
		}
	}

	/**
	 * Stops task
	 * @param {string} taskId Task ID
	 */
	private stopTask(taskId: string): void {
		this.loading = true;
		SchedulerService.stopTask(taskId, new DaemonMessageOptions(null, 10000, 'config.daemon.scheduler.messages.stopTimeout'))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles StopTask Daemon API response
	 * @param response Daemon API response
	 */
	private handleStopTask(response): void {
		if (response.status === 0) {
			this.$toast.success(
				this.$t('config.daemon.scheduler.messages.stopSuccess', {task: response.rsp.taskId}).toString()
			);
			this.getTasks();
		} else {
			this.$toast.error(
				this.$t('config.daemon.scheduler.messages.stopFailed').toString()
			);
		}
	}

	/**
	 * Exports scheduler tasks
	 */
	private exportScheduler(): void {
		this.$store.commit('spinner/SHOW');
		this.service.schedulerExport()
			.then((response: FileResponse<Blob>) => {
				this.$store.commit('spinner/HIDE');
				const fileName = `iqrf-gateway-scheduler_${new Date().toISOString()}.zip`;
				FileDownloader.downloadFileResponse(response, fileName);
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
	 * Creates time string used in scheduler task data table from task time specification
	 * @param {ISchedulerRecordTimeSpec} item Scheduler task time specification
	 * @returns {string} Human readable time message
	 */
	private timeString(item: ISchedulerRecordTimeSpec): string|undefined {
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
			let cron = item.cronTime;
			if (Array.isArray(cron)) {
				cron = cron.join(' ');
			}
			return SchedulerRecord.expressionToString(cron);
		} catch (err) {
			return '';
		}
	}
}
</script>
