<template>
	<v-dialog
		v-model='show'
		scrollable
		persistent
		no-click-animation
		:width='width'
	>
		<template #activator='{ props }'>
			<v-btn
				v-bind='props'
				id='tasks-import-activator'
				color='white'
				size='large'
				:icon='mdiImport'
			/>
			<v-tooltip
				activator='#tasks-import-activator'
				location='bottom'
			>
				{{ $t('components.configuration.daemon.scheduler.actions.import') }}
			</v-tooltip>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Saving'
		>
			<Card>
				<template #title>
					{{ $t('components.configuration.daemon.scheduler.import.title') }}
				</template>
				<v-file-input
					v-model='files'
					accept='.zip'
					:label='$t("components.configuration.daemon.scheduler.import.file")'
					:rules='[
						(v: File|Blob|null) => ValidationRules.required(v, $t("components.configuration.daemon.scheduler.validation.importFileMissing")),
					]'
					:prepend-inner-icon='mdiFileOutline'
					:prepend-icon='null'
					show-size
					required
				/>
				<v-table v-if='processedRecords.length > 0'>
					<thead>
						<tr>
							<th>{{ $t('components.configuration.daemon.scheduler.taskId') }}</th>
							<th>{{ $t('components.configuration.daemon.scheduler.import.imported') }}</th>
							<th>{{ $t('components.configuration.daemon.scheduler.import.reason') }}</th>
						</tr>
					</thead>
					<tbody>
						<tr
							v-for='record of processedRecords'
							:key='record.taskId'
						>
							<td>{{ record.taskId }}</td>
							<td>
								<v-icon
									v-if='record.imported !== undefined'
									size='large'
									:icon='record.imported ? mdiCheckCircleOutline : mdiCloseCircleOutline'
									:color='record.imported ? "success": "error"'
								/>
							</td>
							<td>{{ record.reason }}</td>
						</tr>
					</tbody>
				</v-table>
				<template #actions>
					<v-btn
						color='primary'
						variant='elevated'
						:disabled='!isValid.value || componentState !== ComponentState.Ready'
						@click='onSubmit'
					>
						{{ $t('common.buttons.upload') }}
					</v-btn>
					<v-spacer />
					<v-btn
						color='grey-darken-2'
						variant='elevated'
						@click='close'
					>
						{{ $t('common.buttons.close') }}
					</v-btn>
				</template>
			</Card>
		</v-form>
	</v-dialog>
</template>

<script lang='ts' setup>
import {
	SchedulerService,
	type SchedulerRecord,
	DaemonMessageOptions,
	type DaemonApiResponse,
	SchedulerMessages,
} from '@iqrf/iqrf-gateway-daemon-utils';
import { mdiCheckCircleOutline, mdiCloseCircleOutline, mdiFileOutline, mdiImport } from '@mdi/js';
import { BlobReader, TextWriter, ZipReader } from '@zip.js/zip.js';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import { getModalWidth } from '@/helpers/modal';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useDaemonStore } from '@/store/daemonSocket';
import { ComponentState } from '@/types/ComponentState';

interface TaskImportResult {
	taskId: string,
	imported?: boolean,
	reason?: string,
}
const emit = defineEmits(['imported']);
const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const show: Ref<boolean> = ref(false);
const width = getModalWidth();
const daemonStore = useDaemonStore();
const i18n = useI18n();
const form: Ref<typeof VForm | null> = ref(null);
const files: Ref<File[]> = ref([]);
const msgIds: Ref<string[]> = ref([]);
const importRecords: Ref<SchedulerRecord[]> = ref([]);
const processedRecords: Ref<TaskImportResult[]> = ref([]);

daemonStore.$onAction(
	({ name, after }) => {
		if (name !== 'onMessage') {
			return;
		}
		after((rsp: DaemonApiResponse) => {
			const msgId = rsp.data.msgId;
			const idx = msgIds.value.findIndex((item: string) => msgId === item);
			if (idx === -1) {
				return;
			}
			msgIds.value.splice(idx, 1);
			daemonStore.removeMessage(msgId);

			switch (rsp.mType) {
				case SchedulerMessages.AddTask:
					handleAddTask(rsp);
					break;
				default:
					//
			}
			if (msgIds.value.length === 0) {
				componentState.value = ComponentState.Ready;
				emit('imported');
			}
		});
	},
);

async function extractZip(archive: File): Promise<SchedulerRecord[]> {
	const tasks: SchedulerRecord[] = [];
	const blobReader = new BlobReader(archive);
	const zipReader = new ZipReader(blobReader);
	const files = await zipReader.getEntries();
	for (const file of files) {
		const textWriter = new TextWriter();
		if (file.getData === undefined) {
			continue;
		}
		const content = await file.getData(textWriter);
		const record: SchedulerRecord = JSON.parse(content);
		tasks.push(record);
	}
	return tasks;
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || files.value.length === 0) {
		return;
	}
	importRecords.value = [];
	processedRecords.value = [];
	componentState.value = ComponentState.Saving;
	const file = files.value[0];
	if (['application/zip', 'application/x-zip-compressed'].includes(file.type)) {
		await extractZip(file)
			.then((records: SchedulerRecord[]) => importRecords.value = records)
			.catch(() => toast.error(
				i18n.t('components.configuration.daemon.scheduler.messages.import.archiveInvalid'),
			));
	} else if (file.type === 'application/json') {
		const content = await file.text();
		try {
			importRecords.value.push(JSON.parse(content));
		} catch (e) {
			toast.error(
				i18n.t('components.configuration.daemon.scheduler.messages.import.jsonInvalid'),
			);
			return;
		}
	} else {
		toast.error(
			i18n.t('components.configuration.daemon.scheduler.messages.import.fileInvalid'),
		);
		return;
	}
	uploadRecords();
}

function uploadRecords(): void {
	const options = new DaemonMessageOptions(null, 30000);
	for (const record of importRecords.value) {
		processedRecords.value.push({
			taskId: record.taskId,
		});
		daemonStore.sendMessage(
			SchedulerService.addTask(record, options),
		).then((val: string) => msgIds.value.push(val));
	}
}

function handleAddTask(rsp: DaemonApiResponse): void {
	const taskId = rsp.data.rsp.taskId;
	const idx = processedRecords.value.findIndex((item: TaskImportResult) => item.taskId === taskId);
	if (idx !== -1) {
		if (rsp.data.status !== 0) {
			processedRecords.value[idx].imported = false;
			processedRecords.value[idx].reason = rsp.data.errorStr;
		} else {
			processedRecords.value[idx].imported = true;
		}
	}
}

function close(): void {
	show.value = false;
	importRecords.value = [];
	processedRecords.value = [];
}
</script>
