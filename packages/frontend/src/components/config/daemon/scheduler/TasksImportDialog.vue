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
	<ModalWindow
		v-model='show'
	>
		<template #activator='{ props }'>
			<ICardTitleActionBtn
				v-bind='props'
				:action='Action.Import'
				:tooltip='$t("components.config.daemon.scheduler.actions.import")'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Saving'
			@submit.prevent='onSubmit()'
		>
			<ICard>
				<template #title>
					{{ $t('components.config.daemon.scheduler.import.title') }}
				</template>
				<v-file-input
					v-model='files'
					accept='.zip'
					:label='$t("components.config.daemon.scheduler.import.file")'
					:rules='[
						(v: File|Blob|null) => ValidationRules.required(v, $t("components.config.daemon.scheduler.validation.importFileMissing")),
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
					<ICardActionBtn
						:action='Action.Import'
						:disabled='!isValid.value || componentState !== ComponentState.Ready'
						type='submit'
					/>
					<v-spacer />
					<ICardActionBtn
						:action='Action.Cancel'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { SchedulerMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SchedulerService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { type ApiResponseManagementRsp, type TApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { type SchedulerAddTaskParams, type SchedulerAddTaskResult } from '@iqrf/iqrf-gateway-daemon-utils/types/management';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import {
	Action,
	ICard,
	ICardActionBtn, ICardTitleActionBtn,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiFileOutline } from '@mdi/js';
import { BlobReader, TextWriter, ZipReader } from '@zip.js/zip.js';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import BooleanCheckMarker from '@/components/BooleanCheckMarker.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { validateForm } from '@/helpers/validateForm';
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
const daemonStore = useDaemonStore();
const i18n = useI18n();
const form: Ref<VForm | null> = ref(null);
const files: Ref<File[]> = ref([]);
const msgIds: Ref<string[]> = ref([]);
const importRecords: Ref<SchedulerAddTaskParams[]> = ref([]);
const processedRecords: Ref<TaskImportResult[]> = ref([]);

daemonStore.$onAction(
	({ name, after }) => {
		if (name !== 'onMessage') {
			return;
		}
		after((rsp: TApiResponse) => {
			const msgId = rsp.data.msgId;
			const idx = msgIds.value.indexOf(msgId);
			if (idx === -1) {
				return;
			}
			msgIds.value.splice(idx, 1);
			daemonStore.removeMessage(msgId);
			switch (rsp.mType) {
				case SchedulerMessages.AddTask:
					handleAddTask(rsp as ApiResponseManagementRsp<SchedulerAddTaskResult>);
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

async function extractZip(archive: File): Promise<SchedulerAddTaskParams[]> {
	const tasks: SchedulerAddTaskParams[] = [];
	const blobReader = new BlobReader(archive);
	const zipReader = new ZipReader(blobReader);
	const files = await zipReader.getEntries();
	for (const file of files) {
		const textWriter = new TextWriter();
		if (file.getData === undefined) {
			continue;
		}
		const content = await file.getData(textWriter);
		tasks.push(JSON.parse(content) as SchedulerAddTaskParams);
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
	if (['application/x-zip-compressed', 'application/zip'].includes(file.type)) {
		await extractZip(file)
			.then((records: SchedulerAddTaskParams[]) => importRecords.value = records)
			.catch(() => toast.error(
				i18n.t('components.config.daemon.scheduler.messages.import.archiveInvalid'),
			));
	} else if (file.type === 'application/json') {
		const content = await file.text();
		try {
			importRecords.value.push(JSON.parse(content) as SchedulerAddTaskParams);
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
	const opts = new DaemonMessageOptions(30_000);
	for (const record of importRecords.value) {
		processedRecords.value.push({
			taskId: record.taskId as string,
		});
		msgIds.value.push(
			await daemonStore.sendMessage(
				SchedulerService.addTask(
					{},
					record,
					opts,
				),
			),
		);
	}
}

function handleAddTask(rsp: ApiResponseManagementRsp<SchedulerAddTaskResult>): void {
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
	importRecords.value = [];
	processedRecords.value = [];
}
</script>
