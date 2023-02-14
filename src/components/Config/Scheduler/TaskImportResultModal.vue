<template>
	<CModal
		:show.sync='show'
		color='primary'
		size='lg'
		:close-on-backdrop='false'
		:fade='false'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('config.daemon.scheduler.import.result.title') }}
			</h5>
		</template>
		<CDataTable
			:items='tasks'
			:fields='fields'
			:items-per-page='20'
			:pagination='true'
			:striped='true'
			:sorter='{external: false, resetable: true}'
		>
			<template #success='{item}'>
				<td>
					<CIcon
						:content='item.success ? cilCheckCircle : cilXCircle'
						:class='item.success ? "text-success" : "text-danger"'
						size='xl'
					/>
				</td>
			</template>
			<template #error='{item}'>
				<td>
					{{ item.error }}
				</td>
			</template>
		</CDataTable>
		<template #footer>
			<CButton
				color='primary'
				@click='upload'
			>
				{{ $t('config.daemon.scheduler.import.result.uploadAgain') }}
			</CButton>
			<CButton
				color='secondary'
				@click='hideModal'
			>
				{{ $t('forms.close') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import {CButton, CDataTable, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

import {cilCheckCircle, cilXCircle} from '@coreui/icons';

import {ITaskImportResult} from '@/interfaces/DaemonApi/Scheduler';
import {IField} from '@/interfaces/Coreui';

@Component({
	components: {
		CButton,
		CDataTable,
		CModal,
	},
	data: () => ({
		cilCheckCircle,
		cilXCircle,
	}),
})
export default class TaskImportResultModal extends ModalBase {
	/**
	 * @var {Array<ITaskImportResult>} tasks tasks
	 */
	private tasks: Array<ITaskImportResult> = [];

	/**
	 * @constant {Array<IField>} fields Data table fields
	 */
	private readonly fields: Array<IField> = [
		{
			key: 'clientId',
			label: this.$t('config.daemon.scheduler.form.task.clientId')
		},
		{
			key: 'taskId',
			label: this.$t('config.daemon.scheduler.form.task.taskId')
		},
		{
			key: 'success',
			label: this.$t('config.daemon.scheduler.import.result.table.success'),
			filter: false,
			sorter: false,
		},
		{
			key: 'error',
			label: this.$t('config.daemon.scheduler.import.result.table.error'),
			filter: false,
			sorter: false,
		},
	];

	/**
	 * Shows modal window
	 * @param {Array<ITaskImportResult>} tasks Tasks
	 */
	public showModal(tasks: Array<ITaskImportResult>): void {
		this.tasks = tasks;
		this.openModal();
	}

	/**
	 * Hides modal window
	 */
	private hideModal(): void {
		this.closeModal();
		this.tasks = [];
	}

	/**
	 * Hides modal window and invokes new upload modal
	 */
	private upload(): void {
		this.hideModal();
		this.$emit('new-upload');
	}
}
</script>
