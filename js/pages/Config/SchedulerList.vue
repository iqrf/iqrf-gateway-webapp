<template>
	<div>
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
					<template #timeSpec='{item}'>
						<td>
							{{ timeString(item.timeSpec) }}
						</td>
					</template>
					<template #task='{item}'>
						<td>
							{{ item.task.message.mType }}
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='info'
								size='sm'
								:to='"/config/scheduler/edit/" + item.instance'
							>
								<CIcon :content='$options.icons.edit' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								color='danger'
								size='sm'
								@click='modal.task = item.taskId'
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
				<CButton color='primary' :disabled='importConfig.empty' @click='importScheduler'>
					{{ $t('config.scheduler.buttons.import') }}
				</CButton>
			</template>
		</CModal>
		<CModal
			color='danger'
			:show.sync='modal.task !== null'
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
				{key: 'taskId', label: this.$t('config.scheduler.table.id')},
				{
					key: 'timeSpec',
					label: this.$t('config.scheduler.table.time'),
					filter: false,
					sorter: false,
				},
				{key: 'clientId', label: this.$t('config.scheduler.table.service')},
				{key: 'task', label: this.$t('config.scheduler.table.mType')},
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
		};
	},
	created() {
		this.unsbscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONOPEN') {
				this.getTasks();
				return;
			}
			if (mutation.type === 'SOCKET_ONSEND') {
				if (mutation.payload.mType === 'mngScheduler_List') {
					if (this.taskIds !== null) {
						this.taskIds = null;
						this.tasks = [];
					}
				}
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.mType === 'mngScheduler_List') {
					this.$store.commit('spinner/HIDE');
					if (mutation.payload.data.status === 0) {
						this.taskIds = mutation.payload.data.rsp.tasks;
						this.taskIds.forEach(item => {
							this.getTask(item);
						});
					}
				} else if (mutation.payload.mType === 'mngScheduler_GetTask') {
					if (mutation.payload.data.status === 0) {
						this.tasks.push(mutation.payload.data.rsp);
					}
				} else if (mutation.payload.mType === 'mngScheduler_RemoveTask') {
					this.$store.commit('spinner/HIDE');
					if (mutation.payload.data.status === 0) {
						this.$toast.success(this.$t('config.scheduler.messages.deleteSuccess').toString());
						this.getTasks();
					} else {
						this.$toast.error(this.$t('config.scheduler.messagess.deleteFail').toString());
					}
				}
			}
		});
		if (this.$store.getters.isSocketConnected) {
			this.getTasks();
		}	
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		closeImport() {
			this.importConfig.modal = false;
			this.importConfig.first = true;
		},
		getTasks() {
			this.$store.commit('spinner/SHOW');
			SchedulerService.listTasks();
		},
		getTask(taskId) {
			SchedulerService.getTask(taskId);
		},
		removeTask() {
			this.$store.commit('spinner/SHOW');
			const task = this.modal.task;
			this.modal.task = null;
			SchedulerService.removeTask(task);
		},
		exportScheduler() {
			this.$store.commit('spinner/SHOW');
			SchedulerService.exportConfig()
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					const fileName = 'iqrf-gateway-scheduler_' + + new Date().toISOString().replace(':', ' ');
					const file = fileDownloader(response, 'application/zip', fileName);
					file.click();
				});
		},
		importScheduler () {
			this.import.Modal = false;
			this.$store.commit('spinner/SHOW');
			SchedulerService.importConfig(this.$refs.schedulerImport.$el.children[1].files[0])
				.then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(this.$t('config.scheduler.messages.importSuccess'));
				})
				.catch((error) => {
					this.$store.commit('spinner/HIDE');
					if (error.response.status === 400) {
						this.$toast.error(this.$t('config.scheduler.messages.importInvalidFile'));
					} else if (error.response.status === 415) {
						this.$toast.error(this.$t('config.scheduler.messages.invalidImportFormat'));
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
			if (item.cronTime.length > 0) {
				let timeString = '';
				item.cronTime.forEach(item => {
					timeString += item + ' ';
				});
				return timeString.trim();
			}
		},
	},
	icons: {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
		import: cilArrowTop,
		export: cilArrowBottom,
	}
};
</script>

<style scoped>

</style>