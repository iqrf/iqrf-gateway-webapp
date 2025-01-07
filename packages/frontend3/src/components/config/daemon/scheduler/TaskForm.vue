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
	<ModalWindow v-model='show'>
		<template #activator='{ props }'>
			<CardTitleActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.daemon.scheduler.actions.add")'
			/>
			<DataTableAction
				v-if='action === Action.Edit'
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.daemon.scheduler.actions.edit")'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='[ComponentState.Loading, ComponentState.Saving].includes(componentState)'
			@submit.prevent='onSubmit'
		>
			<Card :action='action'>
				<template #title>
					{{ dialogTitle }}
				</template>
				<TextInput
					v-model='task.taskId'
					:label='$t("components.config.daemon.scheduler.taskId")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.scheduler.validation.taskIdMissing")),
						(v: string) => ValidationRules.uuid(v, $t("components.config.daemon.scheduler.validation.taskIdInvalid")),
					]'
					required
				/>
				<TextInput
					v-model='task.description'
					:label='$t("common.labels.description")'
				/>
				<SelectInput
					v-model='taskType'
					:items='taskTypeOptions'
					:label='$t("components.config.daemon.scheduler.type")'
				/>
				<div v-if='taskType === SchedulerTaskType.ONESHOT'>
					<label for='datetimeinput' class='v-label'>
						{{ $t('components.config.daemon.scheduler.oneshot') }}
					</label>
					<VueDatePicker
						id='datetimeinput'
						v-model='task.timeSpec.startTime'
						model-type='yyyy-MM-dd&apos;T&apos;HH:mm:ssXXX'
						:enable-seconds='true'
						:show-now-button='true'
						:teleport='true'
						:state='datePickerState'
						:utc='true'
					/>
				</div>
				<NumberInput
					v-if='taskType === SchedulerTaskType.PERIODIC'
					v-model.number='task.timeSpec.period'
					:label='$t("components.config.daemon.scheduler.period")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.scheduler.validation.periodMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.scheduler.validation.periodInvalid")),
						(v: number) => ValidationRules.min(v, 1, $t("components.config.daemon.scheduler.validation.periodInvalid")),
					]'
					required
				/>
				<TextInput
					v-if='taskType === SchedulerTaskType.CRON'
					v-model='task.timeSpec.cronTime'
					:label='$t("components.config.daemon.scheduler.cron")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.scheduler.validation.cronMissing")),
						(v: string) => validateCron(v),
					]'
					:description='humanCron'
					required
				>
					<template #append-inner>
						<v-tooltip
							v-if='humanCron'
							location='left'
						>
							<template #activator='{ props }'>
								<v-icon
									v-bind='props'
									:icon='mdiHelpBox'
									size='large'
									color='primary'
								/>
							</template>
							{{ humanCron }}
						</v-tooltip>
					</template>
				</TextInput>
				<v-checkbox
					v-model='task.persist'
					:label='$t("components.config.daemon.scheduler.persist")'
				/>
				<v-checkbox
					v-model='task.enabled'
					:label='$t("components.config.daemon.scheduler.enabled")'
				/>
				<DataTable
					:headers='headers'
					:items='task.task'
					:hover='true'
					:dense='true'
					no-data-text='components.config.daemon.scheduler.noMessages'
				>
					<template #top>
						<v-toolbar color='gray' density='compact' rounded>
							<v-toolbar-title>
								{{ $t('components.config.daemon.scheduler.task.title') }}
							</v-toolbar-title>
							<v-toolbar-items>
								<TaskMessageForm
									:messagings='messagings'
									@save='saveMessage'
								/>
								<v-btn
									color='error'
									:icon='mdiDelete'
									@click='clearMessages'
								/>
							</v-toolbar-items>
						</v-toolbar>
					</template>
					<template #item.message='{ item }'>
						{{ item.message.mType }}
					</template>
					<template #item.messaging='{ item }'>
						{{ item.messaging.join(', ') }}
					</template>
					<template #item.actions='{ item, index }'>
						<TaskMessageForm
							:action='Action.Edit'
							:index='index'
							:messagings='messagings'
							:task='item'
							@save='saveMessage'
						/>
						<v-tooltip
							location='bottom'
						>
							<template #activator='{ props }'>
								<v-icon
									v-bind='props'
									color='error'
									size='large'
									@click='deleteMessage(index)'
								>
									{{ mdiDelete }}
								</v-icon>
							</template>
							{{ $t('components.config.daemon.scheduler.task.actions.delete') }}
						</v-tooltip>
					</template>
				</DataTable>
				<template #actions>
					<CardActionBtn
						:action='action'
						:disabled='!isValid.value || (!datePickerState && taskType === SchedulerTaskType.ONESHOT) || componentState === ComponentState.Saving'
						type='submit'
					/>
					<v-spacer />
					<CardActionBtn :action='Action.Cancel' @click='close' />
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { SchedulerMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SchedulerService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import {
	type DaemonApiResponse,
	type SchedulerRecord,
	type SchedulerRecordTask,
	SchedulerTaskType,
} from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions, SchedulerCron } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { type IqrfGatewayDaemonSchedulerMessagings } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiDelete, mdiHelpBox } from '@mdi/js';
import cron from 'cron-validate';
import { v4 as uuidv4 } from 'uuid';
import { computed, type PropType, ref, type Ref } from 'vue';
import { watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import TaskMessageForm from '@/components/config/daemon/scheduler/TaskMessageForm.vue';
import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useDaemonStore } from '@/store/daemonSocket';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const componentProps = defineProps({
	action: {
		type: String as PropType<Action>,
		default: Action.Add,
		required: false,
	},
	messagings: {
		type: [Object, null] as PropType<IqrfGatewayDaemonSchedulerMessagings | null>,
		required: true,
	},
	schedulerTask: {
		type: [Object, null] as PropType<SchedulerRecord | null>,
		default: null,
		required: false,
	},
});
const emit = defineEmits(['saved']);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
const defaultTask: SchedulerRecord = {
	clientId: SchedulerService.ClientID,
	taskId: '',
	description: '',
	task: [],
	timeSpec: {
		cronTime: '',
		exactTime: true,
		startTime: '',
		period: 60,
		periodic: false,
	},
	persist: true,
	enabled: true,
};
const msgId: Ref<string | null> = ref(null);
const task: Ref<SchedulerRecord> = ref({ ...defaultTask });
const taskType: Ref<SchedulerTaskType> = ref(SchedulerTaskType.ONESHOT);
const humanCron: Ref<string | null> = ref(null);

daemonStore.$onAction(
	({ name, after }) => {
		if (name !== 'onMessage') {
			return;
		}
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Ready;
			switch (rsp.mType) {
				case SchedulerMessages.GetTask.toString():
					handleGetTask(rsp);
					break;
				case SchedulerMessages.AddTask.toString():
					handleSaveTask(rsp);
					break;
				case SchedulerMessages.EditTask.toString():
					handleSaveTask(rsp);
					break;
				default:
				//
			}
		});
	},
);

const taskTypeOptions = [
	{
		value: SchedulerTaskType.ONESHOT,
		title: i18n.t('components.config.daemon.scheduler.types.oneshot'),
	},
	{
		value: SchedulerTaskType.PERIODIC,
		title: i18n.t('components.config.daemon.scheduler.types.periodic'),
	},
	{
		value: SchedulerTaskType.CRON,
		title: i18n.t('components.config.daemon.scheduler.types.cron'),
	},
];

const headers = [
	{ key: 'message', title: i18n.t('components.config.daemon.scheduler.task.mType') },
	{ key: 'messaging', title: i18n.t('components.config.daemon.scheduler.task.messaging') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end' },
];

const datePickerState = computed((): boolean => {
	if (task.value.timeSpec.startTime !== null && task.value.timeSpec.startTime.length !== 0) {
		return true;
	}
	return false;
});

const dialogTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.config.daemon.scheduler.actions.add').toString();
	}
	return i18n.t('components.config.daemon.scheduler.actions.edit').toString();
});

watch(show, (newVal: boolean) => {
	if (!newVal) {
		return;
	}
	if (componentProps.action === Action.Edit && componentProps.schedulerTask) {
		task.value = structuredClone(componentProps.schedulerTask);
		if (Array.isArray(task.value.timeSpec.cronTime)) {
			task.value.timeSpec.cronTime = task.value.timeSpec.cronTime.join(' ').trim();
		}
		setTaskType();
	} else {
		task.value = { ...defaultTask };
		task.value.taskId = uuidv4();
		setTaskType();
	}
	componentState.value = ComponentState.Ready;
});

function setTaskType(): void {
	if (task.value.timeSpec.periodic) {
		taskType.value = SchedulerTaskType.PERIODIC;
	} else if (task.value.timeSpec.exactTime) {
		taskType.value = SchedulerTaskType.ONESHOT;
	} else {
		taskType.value = SchedulerTaskType.CRON;
	}
}

function saveMessage(index: number, recordTask: SchedulerRecordTask): void {
	if (index === null) {
		task.value.task.push(recordTask);
	} else {
		task.value.task[index] = recordTask;
	}
}

function deleteMessage(index: number): void {
	task.value.task.splice(index, 1);
}

function clearMessages(): void {
	task.value.task = [];
}

function handleGetTask(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('TODO GET ERROR HANDLING'),
		);
		return;
	}
	task.value = rsp.data.rsp as SchedulerRecord;
}

function addTask(record: SchedulerRecord): void {
	const options = new DaemonMessageOptions(
		null,
		30_000,
		null,
		() => {msgId.value = null;},
	);
	componentState.value = ComponentState.Saving;
	daemonStore.sendMessage(
		SchedulerService.addTask(record, options),
	).then((val: string) => msgId.value = val);
}

function editTask(record: SchedulerRecord): void {
	const options = new DaemonMessageOptions(
		null,
		30_000,
		null,
		() => {msgId.value = null;},
	);
	componentState.value = ComponentState.Saving;
	daemonStore.sendMessage(
		SchedulerService.editTask(record, options),
	).then((val: string) => msgId.value = val);
}

function handleSaveTask(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('TODO SAVE ERROR HANDLING'),
		);
		return;
	}
	close();
	emit('saved');
}

function validateCron(expression: string): boolean | string {
	if (expression.startsWith('@')) {
		const expr = SchedulerCron.resolveExpressionAlias(expression);
		if (expr === undefined) {
			humanCron.value = null;
			return '';
		}
		expression = expr;
	}
	const cronobj = cron(expression, { preset: SchedulerCron.cronTraits });
	if (cronobj.isValid()) {
		humanCron.value = SchedulerCron.toHumanString(expression);
		return true;
	} else {
		humanCron.value = null;
		return cronobj.error.join(',');
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	const record = { ...task.value };
	delete record.active;
	if (taskType.value === SchedulerTaskType.ONESHOT) {
		record.timeSpec.exactTime = true;
		record.timeSpec.periodic = false;
	} else if (taskType.value === SchedulerTaskType.PERIODIC) {
		record.timeSpec.periodic = true;
		record.timeSpec.exactTime = false;
	} else {
		record.timeSpec.periodic = record.timeSpec.exactTime = false;
		record.timeSpec.cronTime = SchedulerCron.convertCron(record.timeSpec.cronTime);
	}
	if (componentProps.action === Action.Edit && componentProps.schedulerTask?.taskId !== null) {
		record.newTaskId = record.taskId;
		record.taskId = componentProps.schedulerTask!.taskId;
		editTask(record);
	} else {
		delete record.newTaskId;
		addTask(record);
	}
}

function close(): void {
	show.value = false;
	task.value = { ...defaultTask };
}
</script>
