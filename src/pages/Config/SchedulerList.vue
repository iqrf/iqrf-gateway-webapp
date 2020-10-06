<template>
	<div>
		<h1>{{ $t('config.scheduler.title') }}</h1>
		<CCard>
			<CCardHeader>
				<div class='float-right'>
					<CButton
						color='success'
						size='sm'
						to='/config/scheduler/add'
					>
						<CIcon :content='$options.icons.add' />
						{{ $t('table.actions.add') }}
					</CButton>
					<CButton color='primary' size='sm' @click='importConfig.modal = true'>
						<CIcon :content='$options.icons.import' />
						{{ $t('config.scheduler.buttons.import') }}
					</CButton>
					<CButton color='secondary' size='sm' @click='exportScheduler'>
						<CIcon :content='$options.icons.export' />
						{{ $t('config.scheduler.buttons.export') }}
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
						<td v-if='retrieved === "daemon"'>
							{{ timeString(item.timeSpec) }}
						</td>
						<td v-else>
							{{ item.time }}
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
								v-if='retrieved === "daemon"'
								color='info'
								size='sm'
								:to='"/config/scheduler/edit/" + item.taskId'
							>
								<CIcon :content='$options.icons.edit' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								v-else
								color='info'
								size='sm'
								:to='"/config/scheduler/edit/" + item.id'
							>
								<CIcon :content='$options.icons.edit' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								v-if='retrieved === "daemon"'
								color='danger'
								size='sm'
								@click='modal.task = item.taskId'
							>
								<CIcon :content='$options.icons.remove' />
								{{ $t('table.actions.delete') }}
							</CButton>
							<CButton
								v-else
								color='danger'
								size='sm'
								@click='modal.task = item.id'
							>
								<CIcon :content='$options.icons.remove' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			:show.sync='importConfig.modal'
			color='primary'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.scheduler.form.import.title') }}
				</h5>
			</template>
			<CForm>
				<div class='form-group'>
					<CInputFile
						ref='schedulerImport'
						accept='application/json,.zip'
						:label='$t("config.scheduler.form.import.file")'
						@input='isEmpty'
						@click='isEmpty'
					/>
					<p v-if='importConfig.empty && !importConfig.first' style='color: red'>
						{{ $t('config.scheduler.form.import.fileEmpty') }}
					</p>
				</div>
			</CForm>
			<template #footer>
				<CButton color='secondary' @click='closeImport'>
					{{ $t('forms.cancel') }}
				</CButton>
				<CButton
					color='primary'
					:disabled='importConfig.empty'
					@click='importScheduler'
				>
					{{ $t('config.scheduler.buttons.import') }}
				</CButton>
			</template>
		</CModal>
		<CModal
			color='danger'
			:show='modal.task !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.scheduler.messages.deleteTitle') }}
				</h5>
			</template>
			{{ $t('config.scheduler.messages.deletePrompt', {task: modal.task}) }}
			<template #footer>
				<CButton color='danger' @click='modal.task = null'>
					{{ $t('forms.no') }}
				</CButton>
				<CButton color='success' @click='removeTask'>
					{{ $t('forms.yes') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script>
import {CButton, CCard, CCardBody, CCardHeader, CForm, CIcon, CInputFile, CModal} from '@coreui/vue/src';
import {cilPencil, cilPlus, cilTrash, cilArrowTop, cilArrowBottom} from '@coreui/icons';
import {fileDownloader} from '../../helpers/fileDownloader';
import {TextareaAutogrowDirective} from 'vue-textarea-autogrow-directive/src/VueTextareaAutogrowDirective';
import SchedulerService from '../../services/SchedulerService';
import ServiceService from '../../services/ServiceService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {DateTime} from 'luxon';

export default {
	name: 'SchedulerList',
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
	directives: {
		'autogrow': TextareaAutogrowDirective
	},
	data() {
		return {
			fields: [
				{
					key: 'taskId',
					label: this.$t('config.scheduler.table.id'),
				},
				{
					key: 'timeSpec',
					label: this.$t('config.scheduler.table.time'),
					filter: false,
					sorter: false,
				},
				{
					key: 'clientId',
					label: this.$t('config.scheduler.table.service'),
				},
				{
					key: 'task',
					label: this.$t('config.scheduler.table.mType'),
				},
				{
					key: 'actions',
					label: this.$t('table.actions.title'),
					filter: false,
					sorter: false,
				},
			],
			importConfig: {
				modal: false,
				first: true,
				empty: false,
			},
			modal: {
				task: null,
			},
			taskIds: null,
			tasks: [],
			dateFormat: {
				year: 'numeric',
				month: 'short',
				day: 'numeric',
				hour12: false,
				hour: 'numeric',
				minute: 'numeric',
				second: 'numeric',
			},
			useRest: true,
			retrieved: null,
			untouched: true,
		};
	},
	created() {
		this.$store.commit('spinner/SHOW');
		setTimeout(() => {
			if (this.$store.state.webSocketClient.socket.isConnected) {
				this.useRest = false;
			}
			if (this.untouched) {
				this.getTasks();
			}
		}, 1000);
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONOPEN') {
				this.useRest = false;
				if (this.untouched) {
					this.getTasks();
				}
			} else if (mutation.type === 'SOCKET_ONCLOSE' || 
				mutation.type === 'SOCKET_ONERROR') {
				this.useRest = true;
			} else if (mutation.type === 'SOCKET_ONSEND') {
				if (mutation.payload.mType === 'mngScheduler_List') {
					if (this.taskIds !== null) {
						this.taskIds = null;
						this.tasks = [];
					}
				}
			} else if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.mType === 'mngScheduler_List') {
					this.$store.commit('spinner/HIDE');
					if (mutation.payload.data.status === 0) {
						this.taskIds = mutation.payload.data.rsp.tasks;
						this.taskIds.forEach(item => {
							this.getTask(item);
						});
						this.retrieved = 'daemon';
					}
				} else if (mutation.payload.mType === 'mngScheduler_GetTask') {
					if (mutation.payload.data.status === 0) {
						this.tasks.push(mutation.payload.data.rsp);
					}
				} else if (mutation.payload.mType === 'mngScheduler_RemoveTask') {
					this.$store.commit('spinner/HIDE');
					if (mutation.payload.data.status === 0) {
						this.$toast.success(
							this.$t('config.scheduler.messages.deleteSuccess').toString()
						);
						this.getTasks();
					} else {
						this.$toast.error(
							this.$t('config.scheduler.messages.deleteFail').toString()
						);
					}
				}
			}
		});
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		closeImport() {
			this.importConfig.modal = false;
			this.importConfig.first = true;
		},
		displayMTypes(item) {
			try {
				if (this.retrieved === 'rest') {
					if (Array.isArray(item)) {
						return item.join(',');
					} else {
						return item;
					}
				}
				if (Array.isArray(item)) {
					let mTypes = '';
					item.forEach(task => {
						mTypes += task.message.mType + ', ';
					});
					return mTypes.slice(0, -2);
				} else {
					return item.message.mType;
				}
			} catch(err) {
				return '';
			}
		},
		getTasks() {
			this.untouched = false;
			this.$store.commit('spinner/SHOW');
			if (this.useRest) {
				SchedulerService.listTasksREST()
					.then((response) => {
						this.$store.commit('spinner/HIDE');
						this.tasks = response.data;
						this.retrieved = 'rest';
					})
					.catch((error) => FormErrorHandler.schedulerError(error));
			} else {
				SchedulerService.listTasks();
			}
		},
		getTask(taskId) {
			SchedulerService.getTask(taskId);
		},
		removeTask() {
			this.$store.commit('spinner/SHOW');
			const task = this.modal.task;
			this.modal.task = null;
			if (this.useRest) {
				SchedulerService.removeTaskREST(task)
					.then(() => {
						this.$store.commit('spinner/HIDE');
						this.$toast.success(
							this.$t('config.scheduler.messages.deleteSuccess').toString()
						);
						this.getTasks();
					})
					.catch((error) => FormErrorHandler.schedulerError(error));
			} else {
				SchedulerService.removeTask(task);
			}
		},
		exportScheduler() {
			this.$store.commit('spinner/SHOW');
			SchedulerService.exportConfig()
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					const fileName = 'iqrf-gateway-scheduler_' + + new Date().toISOString();
					const file = fileDownloader(response, 'application/zip', fileName);
					file.click();
				});
		},
		importScheduler () {
			this.importConfig.modal = false;
			this.$store.commit('spinner/SHOW');
			SchedulerService.importConfig(this.$refs.schedulerImport.$el.children[1].files[0])
				.then(() => {
					ServiceService.restart('iqrf-gateway-daemon')
						.then(() => {
							this.$store.commit('spinner/HIDE');
							this.$toast.success(
								this.$t('config.scheduler.messages.importSuccess').toString()
							);
							this.$toast.info(
								this.$t('service.iqrf-gateway-daemon.messages.restart')
									.toString()
							);
						})
						.catch((error) => FormErrorHandler.serviceError(error));
				})
				.catch((error) => {
					this.$store.commit('spinner/HIDE');
					if (error.response.status === 400) {
						this.$toast.error(
							this.$t('config.scheduler.messages.importInvalidFile').toString()
						);
					} else if (error.response.status === 415) {
						this.$toast.error(
							this.$t('config.scheduler.messages.importInvalidFormat')
								.toString()
						);
					}
				});
		},
		isEmpty() {
			if (this.importConfig.first) {
				this.importConfig.first = false;
			}
			this.importConfig.empty = this.$refs.schedulerImport.$el.children[1].files.length === 0;
		},
		timeString(item) {
			try {
				if (item.exactTime) {
					return 'oneshot (' + DateTime.fromISO(item.startTime).toLocaleString(this.dateFormat) + ')';
				}
				if (item.periodic) {
					let message = 'every ';
					let date = new Date(0);
					date.setSeconds(item.period);
					if (item.period >= 0 && item.period < 60) {
						message += date.getSeconds() + ' s';
					} else if (item.period < 3600) {
						message += date.getMinutes() + ':' + date.getSeconds() + ' min';
					} else {
						message += date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds() + ' h';
					}
					return message;
				}
				if (item.cronTime.length > 0) {
					let timeString = '';
					item.cronTime.forEach(item => {
						timeString += item + ' ';
					});
					return timeString.trim();
				}
			} catch (err) {
				console.error(err);
				return '';
			}
		},
	},
	icons: {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
		import: cilArrowTop,
		export: cilArrowBottom,
	},
	metaInfo: {
		title: 'config.scheduler.title',
	},
};
</script>
