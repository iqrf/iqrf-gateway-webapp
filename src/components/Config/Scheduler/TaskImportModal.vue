<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<span>
		<CButton
			color='primary'
			size='sm'
			@click='openModal'
		>
			<CIcon :content='cilArrowTop' size='sm' />
			{{ $t('forms.import') }}
		</CButton>
		<CModal
			:show.sync='show'
			color='primary'
			size='lg'
			:close-on-backdrop='false'
			:fade='false'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.daemon.scheduler.import.title') }}
				</h5>
			</template>
			<CForm ref='form'>
				<div class='form-group'>
					<CInputFile
						ref='schedulerInput'
						accept='application/json,.zip'
						:label='$t("config.daemon.scheduler.import.file")'
						@input='isEmpty'
						@click='isEmpty'
					/>
					<p
						v-if='inputEmpty && inputTouched'
						class='text-danger'
					>
						{{ $t('config.daemon.scheduler.import.errors.fileEmpty') }}
					</p>
				</div>
			</CForm>
			<template #footer>
				<CButton
					color='secondary'
					@click='hideModal'
				>
					{{ $t('forms.cancel') }}
				</CButton>
				<CButton
					color='primary'
					:disabled='inputEmpty'
					@click='importScheduler'
				>
					{{ $t('forms.import') }}
				</CButton>
			</template>
		</CModal>
		<TaskImportResultModal
			ref='resultModal'
			v-model='handledTasks'
			@reset='clear'
			@new-upload='newUpload'
		/>
	</span>
</template>

<script lang='ts'>
import {Component, Ref} from 'vue-property-decorator';
import {CButton, CInputFile, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';
import TaskImportResultModal from '@/components/Config/Scheduler/TaskImportResultModal.vue';

import {BlobReader, TextWriter, ZipReader} from '@zip.js/zip.js';
import {cilArrowTop} from '@coreui/icons';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import SchedulerService from '@/services/SchedulerService';

import {ISchedulerRecord, ITaskImportResult} from '@/interfaces/DaemonApi/Scheduler';
import {MutationPayload} from 'vuex';

/**
 * Scheduler task import modal component
 */
@Component({
	components: {
		CButton,
		CInputFile,
		CModal,
		TaskImportResultModal,
	},
	data: () => ({
		cilArrowTop,
	})
})
export default class TaskImportModal extends ModalBase {
	/**
	 * @property {HTMLFormElement} form Form element
	 */
	@Ref('form') form!: HTMLFormElement;

	/**
	 * @property {TaskImportResultModal} resultModal Task import result modal component
	 */
	@Ref('resultModal') resultModal!: TaskImportResultModal;

	/**
	 * @var {boolean} inputEmpty Indicates whether file input is empty or not
	 */
	private inputEmpty = true;

	/**
	 * @var {boolean} inputTouched Indicates that file input hasn't been touched
	 */
	private inputTouched = false;

	/**
	 * @var {Array<string>} msgIds Array of expected response message IDs
	 */
	private msgIds: string[] = [];

	/**
	 * @var {Array<ITaskImportResult>} tasks Tasks to import
	 */
	private tasks: ITaskImportResult[] = [];

	/**
	 * @var {Array<ITaskImportResult>} handledTasks Imported tasks
	 */
	private handledTasks: ITaskImportResult[] = [];

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Registers mutation handler
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONCLOSE') {
				this.clearMsgIds();
				return;
			}
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			const msgId = mutation.payload.data.msgId;
			const idx = this.msgIds.findIndex((item: string) => item === msgId);
			if (idx === -1) {
				return;
			}
			this.msgIds.splice(idx, 1);
			this.$store.dispatch('daemonClient/removeMessage', msgId);
			if (mutation.payload.mType === 'mngScheduler_AddTask') {
				this.handleAddTask(mutation.payload.data);
			} else if (mutation.payload.mType === 'messageError') {
				this.$toast.error(
					this.$t('config.daemon.scheduler.messages.processError').toString()
				);
			}
		});
	}

	/**
	 * Unregisters mutation handler and clears any pending responses
	 */
	beforeDestroy(): void {
		this.unsubscribe();
		this.clearMsgIds();
	}

	/**
	 * Imports scheduler tasks from zip file
	 */
	private async importScheduler(): Promise<void> {
		const file = this.getFile();
		if (file === null) {
			return;
		}
		this.tasks = [];
		this.handledTasks = [];
		let records: Array<ISchedulerRecord> = [];
		if (['application/zip', 'application/x-zip-compressed'].includes(file.type)) {
			await this.extractZip(file)
				.then((data: ISchedulerRecord[]) => {
					records = data;
				})
				.catch(() => {
					this.$toast.error(
						this.$t('config.daemon.scheduler.import.errors.invalidArchive').toString()
					);
				});
		} else if (file.type === 'application/json') {
			const content = await file.text();
			try {
				const record: ISchedulerRecord = JSON.parse(content);
				this.tasks.push({
					clientId: record.clientId,
					taskId: record.taskId,
					success: false
				});
				records.push(record);
			} catch (error) {
				this.$toast.error(
					this.$t('config.daemon.scheduler.import.errors.invalidJson').toString()
				);
				return;
			}
		} else {
			this.$toast.error(
				this.$t('config.daemon.scheduler.import.errors.invalidFile').toString()
			);
			return;
		}
		for (const record of records) {
			SchedulerService.addTask(record, new DaemonMessageOptions(null, 30000, 'config.daemon.scheduler.messages.processError'))
				.then((msgId: string) => this.msgIds.push(msgId));
		}
	}

	/**
	 * Extracts contents of zip file and converts them to array of tasks to import
	 * @param {File} zipFile Zip archive to extract data from
	 */
	private async extractZip(zipFile: File): Promise<ISchedulerRecord[]> {
		const blobReader = new BlobReader(zipFile);
		const zipReader = new ZipReader(blobReader);
		const files = await zipReader.getEntries();
		const records: ISchedulerRecord[] = [];
		for (const file of files) {
			const textWriter = new TextWriter();
			const content = await file.getData(textWriter);
			const record: ISchedulerRecord = JSON.parse(content);
			records.push(record);
			this.tasks.push({
				clientId: record.clientId,
				taskId: record.taskId,
				success: false
			});
		}
		return records;
	}

	/**
	 * Handles Daemon API task import response and stores results
	 * @param response Task import response
	 */
	private handleAddTask(response): void {
		const clientId = response.rsp.clientId;
		const taskId = response.rsp.taskId;
		const success = response.status === 0;
		const idx = this.tasks.findIndex((item: ITaskImportResult) => item.clientId === clientId && item.taskId === taskId);
		if (idx !== -1) {
			this.tasks[idx].success = success;
			if (!success) {
				this.tasks[idx].error = response.errorStr;
			}
			this.handledTasks.push(this.tasks[idx]);
			this.tasks.splice(idx, 1);
		}
		if (this.tasks.length === 0) {
			this.resultModal.showModal(this.handledTasks);
			this.closeModal();
			this.$emit('imported');
		}
	}

	/**
	 * Retrieves file from input if one was selected
	 * @return {File|null} Uploaded zip archive
	 */
	private getFile(): File|null {
		const input = (this.$refs.schedulerInput as CInputFile).$el.children[1] as HTMLInputElement;
		const filelist = (input.files as FileList);
		if (filelist.length === 0) {
			return null;
		}
		return filelist[0];
	}

	/**
	 * Checks if file input is empty
	 */
	private isEmpty(): void {
		if (!this.inputTouched) {
			this.inputTouched = true;
		}
		this.inputEmpty = this.getFile() === null;
	}

	/**
	 * Remove Daemon API message ID from expected responses
	 * @param {string} msgId Message ID to remove
	 */
	private removeMsgId(msgId: string): void {
		const idx = this.msgIds.findIndex((item: string) => item === msgId);
		if (idx === -1) {
			return;
		}
		this.msgIds.splice(idx, 1);
	}

	/**
	 * Clears all Daemon API message IDs from expected responses
	 */
	private clearMsgIds(): void {
		for (const msgId of this.msgIds) {
			this.$store.dispatch('daemonClient/removeMessage', msgId);
		}
		this.msgIds = [];
	}

	/**
	 * Hides modal and clears form data
	 */
	private hideModal(): void {
		this.closeModal();
		this.clear();
	}

	/**
	 * Clears form data
	 */
	private clear(): void {
		this.form.reset();
		this.inputEmpty = true;
		this.inputTouched = false;
	}

	/**
	 * Clears form data and shows modal window
	 */
	private newUpload(): void {
		this.clear();
		this.show = true;
	}
}
</script>
