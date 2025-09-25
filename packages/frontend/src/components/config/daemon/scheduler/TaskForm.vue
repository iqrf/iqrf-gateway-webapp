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
	<IModalWindow v-model='show'>
		<template #activator='{ props }'>
			<IActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
				container-type='card-title'
				:tooltip='$t("components.config.daemon.scheduler.actions.add")'
				:disabled='disabled'
			/>
			<IDataTableAction
				v-if='action === Action.Edit'
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.daemon.scheduler.actions.edit")'
				:disabled='disabled'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='[ComponentState.Loading, ComponentState.Action].includes(componentState)'
			@submit.prevent='onSubmit()'
		>
			<ICard :action='action'>
				<template #title>
					{{ dialogTitle }}
				</template>
				<ITextInput
					v-model='task.taskId'
					:label='$t("components.config.daemon.scheduler.taskId")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.scheduler.validation.taskId.required")),
						(v: string) => ValidationRules.uuid(v, $t("components.config.daemon.scheduler.validation.taskId.uuid")),
					]'
					required
				/>
				<ITextInput
					v-model='task.description'
					:label='$t("common.labels.description")'
				/>
				<ISelectInput
					v-model='taskType'
					:items='taskTypeOptions'
					:label='$t("components.config.daemon.scheduler.type")'
				/>
				<IDateTimeInput
					v-if='taskType === SchedulerTaskType.ONESHOT'
					v-model='oneshotTime'
					:label='$t("components.config.daemon.scheduler.oneshot")'
				/>
				<INumberInput
					v-if='taskType === SchedulerTaskType.PERIODIC'
					v-model='task.timeSpec.period'
					:label='$t("components.config.daemon.scheduler.period")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.scheduler.validation.period.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.scheduler.validation.period.integer")),
						(v: number) => ValidationRules.min(v, 1, $t("components.config.daemon.scheduler.validation.period.min")),
					]'
					:min='1'
					required
				/>
				<ITextInput
					v-if='taskType === SchedulerTaskType.CRON'
					v-model='task.timeSpec.cronTime'
					:label='$t("components.config.daemon.scheduler.cron")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.scheduler.validation.cron.required")),
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
				</ITextInput>
				<v-checkbox
					v-model='task.persist'
					:label='$t("components.config.daemon.scheduler.persist")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='task.enabled'
					:label='$t("components.config.daemon.scheduler.enabled")'
					density='compact'
				/>
				<IDataTable
					:headers='headers'
					:items='Array.isArray(task.task) ? task.task : [task.task]'
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
									:disabled='componentState === ComponentState.Action'
									@save='(index: number, t: SchedulerTask) => saveMessage(index, t)'
								/>
								<IActionBtn
									:action='Action.Delete'
									container-type='card-title'
									:disabled='componentState === ComponentState.Action'
									:tooltip='$t("components.config.daemon.scheduler.task.actions.deleteAll")'
									@click='clearMessages()'
								/>
							</v-toolbar-items>
						</v-toolbar>
					</template>
					<template #item.message='{ item }'>
						{{ item.message.mType }}
					</template>
					<template #item.messaging='{ item }'>
						{{ formatMessagingInstances(item.messaging) }}
					</template>
					<template #item.actions='{ item, index }'>
						<TaskMessageForm
							:action='Action.Edit'
							:index='index'
							:messagings='messagings'
							:task='item'
							:disabled='componentState === ComponentState.Action'
							@save='(index: number, t: SchedulerTask) => saveMessage(index, t)'
						/>
						<IDataTableAction
							:action='Action.Delete'
							:tooltip='$t("components.config.daemon.scheduler.task.actions.delete")'
							:disabled='componentState === ComponentState.Action'
							@click='deleteMessage(index)'
						/>
					</template>
				</IDataTable>
				<template #actions>
					<IActionBtn
						:action='action'
						:loading='componentState === ComponentState.Action'
						:disabled='!isValid.value || !oneshotTimeValid'
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
import { SchedulerMessages, SchedulerTaskType } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SchedulerService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import {
	type ApiResponseManagementRsp,
	type MessagingInstance,
	type TApiResponse,
} from '@iqrf/iqrf-gateway-daemon-utils/types';
import {
	type SchedulerAddTaskParams,
	type SchedulerAddTaskResult,
	type SchedulerEditTaskParams,
	type SchedulerEditTaskResult,
	type SchedulerGetTaskResult,
	type SchedulerTask,
} from '@iqrf/iqrf-gateway-daemon-utils/types/management';
import { DaemonMessageOptions, SchedulerCron } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { type IqrfGatewayDaemonMessagingInstances } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
	IDataTableAction,
	IDateTimeInput,
	IModalWindow,
	INumberInput,
	ISelectInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiHelpBox } from '@mdi/js';
import cron from 'cron-validate';
import { DateTime } from 'luxon';
import { v4 as uuidv4 } from 'uuid';
import { computed, type PropType, ref, type Ref, toRaw } from 'vue';
import { watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import TaskMessageForm from '@/components/config/daemon/scheduler/TaskMessageForm.vue';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const componentProps = defineProps({
	action: {
		type: String as PropType<Action>,
		default: Action.Add,
		required: false,
	},
	messagings: {
		type: [Object, null] as PropType<IqrfGatewayDaemonMessagingInstances | null>,
		required: true,
	},
	schedulerTask: {
		type: [Object, null] as PropType<SchedulerGetTaskResult | null>,
		default: null,
		required: false,
	},
	disabled: {
		type: Boolean,
		default: false,
		required: false,
	},
});
const emit = defineEmits(['saved']);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
const defaultTask: SchedulerAddTaskParams = {
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
const task: Ref<SchedulerAddTaskParams | SchedulerEditTaskParams> = ref({ ...defaultTask });
const taskType: Ref<SchedulerTaskType> = ref(SchedulerTaskType.ONESHOT);
const humanCron: Ref<string | null> = ref(null);
const oneshotTime: Ref<DateTime | null> = ref(null);

daemonStore.$onAction(
	({ name, after }) => {
		if (name !== 'onMessage') {
			return;
		}
		after((rsp: TApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			switch (rsp.mType) {
				case SchedulerMessages.AddTask:
					handleAddTask(rsp as ApiResponseManagementRsp<SchedulerAddTaskResult>);
					break;
				case SchedulerMessages.EditTask:
					handleEditTask(rsp as ApiResponseManagementRsp<SchedulerEditTaskResult>);
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

const oneshotTimeValid = computed(() => {
	return taskType.value !== SchedulerTaskType.ONESHOT || oneshotTime.value !== null;
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
	if (componentProps.action === Action.Edit && componentProps.schedulerTask !== null) {
		task.value = structuredClone(toRaw(componentProps.schedulerTask));
		if (Array.isArray(task.value.timeSpec.cronTime)) {
			task.value.timeSpec.cronTime = task.value.timeSpec.cronTime.join(' ').trim();
		}
		if (task.value.timeSpec.startTime.length > 0) {
			oneshotTime.value = DateTime.fromISO(task.value.timeSpec.startTime);
		}
		setTaskType();
	} else {
		task.value = { ...defaultTask };
		task.value.taskId = uuidv4();
		setTaskType();
	}
});

function formatMessagingInstances(instances: MessagingInstance[]): string {
	instances = instances.sort((a: MessagingInstance, b: MessagingInstance) => {
		const res = a.type.localeCompare(b.type);
		if (res !== 0) {
			return res;
		}
		return a.instance.localeCompare(b.instance);
	});
	let lastType: string|null = null;
	let out = '';
	for (const [index, item] of instances.entries()) {
		if (lastType !== item.type) {
			lastType = item.type;
			out += `[${item.type.toUpperCase()}] `;
		}
		out += item.instance;
		if (index < instances.length - 1) {
			out += ', ';
		}
	}
	return out;
}

function setTaskType(): void {
	if (task.value.timeSpec.periodic) {
		taskType.value = SchedulerTaskType.PERIODIC;
	} else if (task.value.timeSpec.exactTime) {
		taskType.value = SchedulerTaskType.ONESHOT;
	} else {
		taskType.value = SchedulerTaskType.CRON;
	}
}

function saveMessage(index: number, recordTask: SchedulerTask): void {
	if (index === null) {
		(task.value.task as SchedulerTask[]).push(recordTask);
	} else {
		(task.value.task as SchedulerTask[])[index] = recordTask;
	}
}

function deleteMessage(index: number): void {
	(task.value.task as SchedulerTask[]).splice(index, 1);
}

function clearMessages(): void {
	task.value.task = [];
}

async function addTask(record: SchedulerAddTaskParams): Promise<void> {
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		30_000,
		i18n.t('components.config.daemon.scheduler.messages.add.timeout'),
		() => {
			componentState.value = ComponentState.Idle;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		SchedulerService.addTask(
			{},
			record,
			opts,
		),
	);
}

function handleAddTask(rsp: ApiResponseManagementRsp<SchedulerAddTaskResult>): void {
	if (rsp.data.status !== 0) {
		componentState.value = ComponentState.Idle;
		toast.error(
			i18n.t('components.config.daemon.scheduler.messages.add.failed'),
		);
		return;
	}
	componentState.value = ComponentState.Idle;
	toast.success(
		i18n.t('components.config.daemon.scheduler.messages.add.success', { id: rsp.data.rsp.taskId }),
	);
	close();
	emit('saved');
}

async function editTask(record: SchedulerEditTaskParams): Promise<void> {
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		30_000,
		i18n.t('components.config.daemon.scheduler.messages.edit.timeout'),
		() => {
			componentState.value = ComponentState.Idle;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		SchedulerService.editTask(
			{},
			record,
			opts,
		),
	);
}

function handleEditTask(rsp: ApiResponseManagementRsp<SchedulerEditTaskResult>): void {
	if (rsp.data.status !== 0) {
		componentState.value = ComponentState.Idle;
		toast.error(
			i18n.t('components.config.daemon.scheduler.messages.edit.failed', { id: task.value.taskId }),
		);
		return;
	}
	componentState.value = ComponentState.Idle;
	toast.success(
		i18n.t('components.config.daemon.scheduler.messages.edit.success', { id: task.value.taskId }),
	);
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
	const record = { ...task.value } as SchedulerGetTaskResult;
	delete record.active;
	if (taskType.value === SchedulerTaskType.ONESHOT) {
		record.timeSpec.exactTime = true;
		record.timeSpec.periodic = false;
		record.timeSpec.startTime = oneshotTime.value!.toJSDate().toISOString();
	} else if (taskType.value === SchedulerTaskType.PERIODIC) {
		record.timeSpec.periodic = true;
		record.timeSpec.exactTime = false;
	} else {
		record.timeSpec.periodic = record.timeSpec.exactTime = false;
		record.timeSpec.cronTime = SchedulerCron.convertCron(record.timeSpec.cronTime as string);
	}
	if (componentProps.action === Action.Edit && componentProps.schedulerTask?.taskId !== null) {
		(record as SchedulerEditTaskParams).newTaskId = record.taskId;
		record.taskId = componentProps.schedulerTask!.taskId;
		await editTask(record);
	} else {
		await addTask(record);
	}
}

function close(): void {
	show.value = false;
	task.value = { ...defaultTask };
}
</script>
