<template>
	<CCard>
		<CCardBody>
			<table class='table table-hover table-striped table-bordered table-sm'>
				<thead>
					<tr>
						<th colspan='5'>
							<span style='float: right;'>
								<CButton color='success' size='sm' @click='openAddModal'>
									{{ $t('config.scheduler.buttons.add') }}
								</CButton>
								<CButton color='primary' size='sm' @click='importModal = true'>
									{{ $t('config.scheduler.buttons.import') }}
								</CButton>
								<CButton color='secondary' size='sm' @click.prevent='exportScheduler'>
									{{ $t('config.scheduler.buttons.export') }}
								</CButton>
							</span>
						</th>
					</tr>
					<tr>
						<th v-for='field of fields' :key='field.key' :class='field.classes'>
							{{ field.label }}
						</th>
					</tr>
				</thead>
				<tbody v-if='tasks !== null && tasks.length === 0'>
					<tr>
						<td colspan='5'>
							{{ $t('config.scheduler.emptyTasks') }}
						</td>
					</tr>
				</tbody>
				<tbody v-else>
					<tr v-for='task of tasks' :key='task.id'>
						<td class='text-right'>{{ task.id }}</td>
						<td>{{ task.time }}</td>
						<td>{{ task.service }}</td>
						<td>{{ task.mTypes }}</td>
						<td>
							<CButton color='info' size='sm'>
								{{ $t('config.scheduler.buttons.edit') }}
							</CButton>
							<CButton color='danger' size='sm'>
								{{ $t('config.scheduler.buttons.remove') }}
							</CButton>
						</td>
					</tr>
				</tbody>
			</table>
		</CCardBody>
		<CModal
			:show.sync='addModal'
			color='success'
			:title='$t("config.scheduler.form.addOrEdit.addTitle")'
			size='lg'
		>
			<CForm>
				<CInput
					v-model='taskToAdd.id'
					type='number'
					:label='$t("config.scheduler.form.addOrEdit.taskId")'
				/>
				<CSelect
					:value.sync='taskToAdd.service'
					:options='[
						{value: "SchedulerMessaging", label: "SchedulerMessaging"}
					]'
					placeholder='Select service'
				/>
				<CInput
					v-model='taskToAdd.cronTime'
					:label='$t("config.scheduler.form.addOrEdit.timeSpec.cronTime")'
				/>
				<CInputCheckbox
					:checked.sync='taskToAdd.exactTime'
					:label='$t("config.scheduler.form.addOrEdit.timeSpec.exactTime")'
				/>
				<CInputCheckbox
					:checked.sync='taskToAdd.periodic'
					:label='$t("config.scheduler.form.addOrEdit.timeSpec.periodic")'
				/>
				<CInput
					v-model.number='taskToAdd.period'
					type='number'
					min='0'
					:label='$t("config.scheduler.form.addOrEdit.timeSpec.period")'
				/>
				<CInput
					v-model='taskToAdd.startTime'
					:label='$t("config.scheduler.form.addOrEdit.timeSpec.startTime")'
				/>
				<h3>{{ $t('config.scheduler.form.addOrEdit.message.title') }}</h3><hr>
				<CSelect
					:value.sync='taskToAdd.messaging'
					:options='[
						{value: "MqMessaging", label: "MqMessaging"},
					]'
					placeholder='Select messaging'
				/>
				<CTextarea
					v-model='taskToAdd.message'
					v-autogrow
					:label='$t("config.scheduler.form.addOrEdit.message.label")'
				/>
				<CButton
					color='success'
				>
					{{ $t('config.scheduler.form.addOrEdit.message.add') }}
				</CButton>
				<CButton color='primary'>
					{{ $t('forms.save') }}
				</CButton>
				<CButton color='primary'>
					{{ $t('forms.saveRestart') }}
				</CButton>
			</CForm>
		</CModal>
		<CModal
			:show.sync='importModal'
			color='primary'
			:title='$t("config.scheduler.form.import.title")'
		>
			<CForm>
				<div class='form-group'>
					<CInputFile
						ref='schedulerImport'
						:label='$t("config.scheduler.form.import.file")'
						@input='isEmpty'
						@click='isEmpty'
					/>
					<p v-if='importEmpty && !importFirst' style='color: red'>
						{{ $t('config.scheduler.form.import.fileEmpty') }}
					</p>
				</div>
			</CForm>
			<template #footer>
				<CButton color='primary' :disabled='importEmpty' @click='importScheduler'>
					{{ $t('config.scheduler.buttons.import') }}
				</CButton>
			</template>
		</CModal>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CForm, CInput, CInputFile, CModal, CSelect, CTextarea} from '@coreui/vue/src';
import {fileDownloader} from '../../helpers/fileDownloader';
import {TextareaAutogrowDirective} from 'vue-textarea-autogrow-directive/src/VueTextareaAutogrowDirective';
import SchedulerService from '../../services/SchedulerService';

export default {
	name: 'Scheduler',
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CInputFile,
		CModal,
		CSelect,
		CTextarea
	},
	directives: {
		'autogrow': TextareaAutogrowDirective
	},
	data() {
		return {
			fields: [
				{key: 'id', label: 'ID', classes: 'text-right'},
				{key: 'time', label: 'Time', classes: 'text-left'},
				{key: 'service', label: 'Service', classes: 'text-left'},
				{key: 'mType', label: 'Message types', classes: 'text-left'},
				{key: 'action', label: 'Actions', classes: 'text-left'}
			],
			addModal: false,
			importModal: false,
			importFirst: true,
			importEmpty: true,
			tasks: null,
			taskToEdit: null,
			taskToAdd: {
				id: null,
				service: null,
				cronTime: null,
				exactTime: false,
				periodic: false,
				period: 0,
				startTime: null,
				messaging: null,
				message: null,
			}
		};
	},
	created() {
		this.getTasks();
	},
	methods: {
		getTasks() {
			SchedulerService.getTasks()
				.then((response) => {
					this.tasks = response.data;
				});
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
			this.importModal = false;
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
			if (this.importFirst) {
				this.importFirst = false;
			}
			this.importEmpty = this.$refs.schedulerImport.$el.children[1].files.length === 0;
		},
		openAddModal() {
			this.taskToAdd.id = Date.now();
			this.taskToAdd.service = null;
			this.taskToAdd.messaging = null;
			this.addModal = true;
		}
	}
};
</script>

<style scoped>

</style>