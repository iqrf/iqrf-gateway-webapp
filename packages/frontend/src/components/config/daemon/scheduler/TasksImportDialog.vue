<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
	<IModalWindow
		v-model='show'
		persistent
	>
		<template #activator='{ props }'>
			<IActionBtn
				v-bind='props'
				:action='Action.Import'
				container-type='card-title'
				:disabled='disabled'
				:tooltip='$t("components.config.daemon.scheduler.actions.import")'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Action'
			@submit.prevent='onSubmit()'
		>
			<ICard>
				<template #title>
					{{ $t('components.config.daemon.scheduler.import.title') }}
				</template>
				<v-file-input
					v-model='file'
					accept='.zip'
					:label='$t("components.config.daemon.scheduler.import.file")'
					:rules='[
						(v: File|Blob|null) => ValidationRules.required(v, $t("components.config.daemon.scheduler.validation.importFile.required")),
					]'
					:prepend-inner-icon='mdiFileOutline'
					:prepend-icon='undefined'
					show-size
					required
				/>
				<v-table v-if='processedRecords.length > 0'>
					<thead>
						<tr>
							<th>{{ $t('components.config.daemon.scheduler.taskId') }}</th>
							<th>{{ $t('components.config.daemon.scheduler.import.imported') }}</th>
							<th>{{ $t('components.config.daemon.scheduler.import.reason') }}</th>
						</tr>
					</thead>
					<tbody>
						<tr
							v-for='record of processedRecords'
							:key='record.taskId'
						>
							<td>{{ record.taskId }}</td>
							<td>
								<BooleanCheckMarker
									v-if='record.imported !== undefined'
									:value='record.imported'
								/>
							</td>
							<td>{{ record.reason }}</td>
						</tr>
					</tbody>
				</v-table>
				<template #actions>
					<IActionBtn
						:action='Action.Import'
						:loading='componentState === ComponentState.Action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						:disabled='componentState === ComponentState.Action'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { SchedulerMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SchedulerService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { type DaemonApiResponse, SchedulerRecord } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IModalWindow,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiFileOutline } from '@mdi/js';
import { BlobReader, TextWriter, ZipReader } from '@zip.js/zip.js';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import BooleanCheckMarker from '@/components/BooleanCheckMarker.vue';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';

interface TaskImportResult {
	taskId: string,
	imported?: boolean,
	reason?: string,
}
defineProps({
	disabled: {
		type: Boolean,
		required: false,
		default: false,
	},
});
const emit = defineEmits(['imported']);
const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const show: Ref<boolean> = ref(false);
const daemonStore = useDaemonStore();
const i18n = useI18n();
const form: Ref<VForm | null> = ref(null);
const file: Ref<File | null> = ref(null);
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
			const idx = msgIds.value.indexOf(msgId);
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
	const zipReader = new ZipReader(new BlobReader(archive));
	const entries = await zipReader.getEntries();
	for (const entry of entries) {
		if (entry.directory || !entry.filename.endsWith('.json')) {
			continue;
		}
		const content = await entry?.getData(new TextWriter());
		try {
			tasks.push(JSON.parse(content));
		} catch {
			continue;
		}
	}
	await zipReader.close();
	return tasks;
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || file.value === null) {
		return;
	}
	importRecords.value = [];
	processedRecords.value = [];
	componentState.value = ComponentState.Action;
	if (['application/x-zip-compressed', 'application/zip'].includes(file.value.type)) {
		await extractZip(file.value)
			.then((records: SchedulerRecord[]) => importRecords.value = records)
			.catch(() => toast.error(
				i18n.t('components.config.daemon.scheduler.messages.import.archiveInvalid'),
			));
	} else if (file.value.type === 'application/json') {
		const content = await file.value.text();
		try {
			importRecords.value.push(JSON.parse(content) as SchedulerRecord);
		} catch {
			toast.error(
				i18n.t('components.config.daemon.scheduler.messages.import.jsonInvalid'),
			);
			return;
		}
	} else {
		toast.error(
			i18n.t('components.config.daemon.scheduler.messages.import.fileInvalid'),
		);
		return;
	}
	await uploadRecords();
}

async function uploadRecords(): Promise<void> {
	const opts = new DaemonMessageOptions(null, 30_000);
	for (const record of importRecords.value) {
		processedRecords.value.push({
			taskId: record.taskId as string,
		});
		msgIds.value.push(
			await daemonStore.sendMessage(
				SchedulerService.addTask(record, opts),
			),
		);
	}
}

function handleAddTask(rsp: DaemonApiResponse): void {
	const taskId = rsp.data.rsp.taskId as string;
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
	file.value = null;
	importRecords.value = [];
	processedRecords.value = [];
}
</script>
