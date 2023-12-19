<template>
	<v-dialog
		v-model='show'
		persistent
		scrollable
		no-click-animation
		:width='width'
	>
		<template #activator='{ props }'>
			<v-btn
				v-if='action === FormAction.Add'
				id='add-activator'
				v-bind='props'
				:color='iconColor'
				:icon='activatorIcon'
			/>
			<v-tooltip
				v-if='action === FormAction.Add'
				activator='#add-activator'
				location='bottom'
			>
				{{ $t('components.configuration.daemon.scheduler.actions.add') }}
			</v-tooltip>
			<v-icon
				v-if='action === FormAction.Edit'
				v-bind='props'
				:color='iconColor'
				:icon='activatorIcon'
				size='large'
				class='me-2'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='[ComponentState.Loading, ComponentState.Saving].includes(componentState)'
			@submit.prevent='onSubmit'
		>
			<Card>
				<template #title>
					{{ dialogTitle }}
				</template>
				<TextInput
					v-model='task.taskId'
					:label='$t("components.configuration.daemon.scheduler.taskId")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.scheduler.validation.taskIdMissing")),
						(v: string) => ValidationRules.uuid(v, $t("components.configuration.daemon.scheduler.validation.taskIdInvalid")),
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
					:label='$t("components.configuration.daemon.scheduler.type")'
				/>
				<div v-if='taskType === SchedulerTaskType.ONESHOT'>
					<label for='datetimeinput' class='v-label'>
						{{ $t('components.configuration.daemon.scheduler.oneshot') }}
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
				<TextInput
					v-if='taskType === SchedulerTaskType.PERIODIC'
					v-model.number='task.timeSpec.period'
					type='number'
					:label='$t("components.configuration.daemon.scheduler.period")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.scheduler.validation.periodMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.scheduler.validation.periodInvalid")),
						(v: number) => ValidationRules.min(v, 1, $t("components.configuration.daemon.scheduler.validation.periodInvalid")),
					]'
					required
				/>
				<TextInput
					v-if='taskType === SchedulerTaskType.CRON'
					v-model='task.timeSpec.cronTime'
					:label='$t("components.configuration.daemon.scheduler.cron")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.scheduler.validation.cronMissing")),
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
					:label='$t("components.configuration.daemon.scheduler.persist")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='task.enabled'
					:label='$t("components.configuration.daemon.scheduler.enabled")'
					density='compact'
				/>
				<DataTable
					:headers='headers'
					:items='task.task'
					:hover='true'
					:dense='true'
					no-data-text='components.configuration.daemon.scheduler.noMessages'
				>
					<template #top>
						<v-toolbar color='primary' density='compact' rounded>
							<v-toolbar-title>
								{{ $t('components.configuration.daemon.scheduler.task.title') }}
							</v-toolbar-title>
							<v-toolbar-items>
								<TaskMessageForm
									:messagings='messagings'
									@save='saveMessage'
								/>
								<v-btn
									color='white'
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
							:action='FormAction.Edit'
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
							{{ $t('components.configuration.daemon.scheduler.task.actions.delete') }}
						</v-tooltip>
					</template>
				</DataTable>
				<template #actions>
					<v-btn
						color='primary'
						type='submit'
						variant='elevated'
						:disabled='!isValid.value || (!datePickerState && taskType === SchedulerTaskType.ONESHOT) || componentState === ComponentState.Saving'
					>
						{{ $t(`common.buttons.${action}`) }}
					</v-btn>
					<v-spacer />
					<v-btn
						color='grey-darken-2'
						variant='elevated'
						:disabled='componentState === ComponentState.Saving'
						@click='close'
					>
						{{ $t('common.buttons.cancel') }}
					</v-btn>
				</template>
			</Card>
		</v-form>
	</v-dialog>
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
import { mdiDelete, mdiHelpBox, mdiPencil, mdiPlus } from '@mdi/js';
import cron from 'cron-validate';
import { v4 as uuidv4 } from 'uuid';
import { type PropType, type Ref, ref, computed } from 'vue';
import { watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import TaskMessageForm from '@/components/config/daemon/scheduler/TaskMessageForm.vue';
import DataTable from '@/components/DataTable.vue';
import SelectInput from '@/components/SelectInput.vue';
import TextInput from '@/components/TextInput.vue';
import { FormAction } from '@/enums/controls';
import { getModalWidth } from '@/helpers/modal';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useDaemonStore } from '@/store/daemonSocket';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const componentProps = defineProps({
	action: {
		type: String as PropType<FormAction>,
		default: FormAction.Add,
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
const width = getModalWidth();
const show: Ref<boolean> = ref(false);
const form: Ref<typeof VForm | null> = ref(null);
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
const task: Ref<SchedulerRecord> = ref({...defaultTask});
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
				case SchedulerMessages.GetTask:
					handleGetTask(rsp);
					break;
				case SchedulerMessages.AddTask:
					handleSaveTask(rsp);
					break;
				case SchedulerMessages.EditTask:
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
		title: i18n.t('components.configuration.daemon.scheduler.types.oneshot'),
	},
	{
		value: SchedulerTaskType.PERIODIC,
		title: i18n.t('components.configuration.daemon.scheduler.types.periodic'),
	},
	{
		value: SchedulerTaskType.CRON,
		title: i18n.t('components.configuration.daemon.scheduler.types.cron'),
	},
];

const headers = [
	{key: 'message', title: i18n.t('components.configuration.daemon.scheduler.task.mType')},
	{key: 'messaging', title: i18n.t('components.configuration.daemon.scheduler.task.messaging')},
	{key: 'actions', title: i18n.t('common.columns.actions'), align: 'end'},
];

const datePickerState = computed((): boolean => {
	if (task.value.timeSpec.startTime !== null && task.value.timeSpec.startTime.length !== 0) {
		return true;
	}
	return false;
});

const iconColor = computed(() => {
	if (componentProps.action === FormAction.Add) {
		return 'white';
	}
	return 'info';
});

const activatorIcon = computed(() => {
	if (componentProps.action === FormAction.Add) {
		return mdiPlus;
	}
	return mdiPencil;
});

const dialogTitle = computed(() => {
	if (componentProps.action === FormAction.Add) {
		return i18n.t('components.configuration.daemon.scheduler.actions.add').toString();
	}
	return i18n.t('components.configuration.daemon.scheduler.actions.edit').toString();
});

watch(show, (newVal: boolean) => {
	if (!newVal) {
		return;
	}
	if (componentProps.action === FormAction.Edit && componentProps.schedulerTask) {
		task.value = JSON.parse(JSON.stringify(componentProps.schedulerTask));
		if (Array.isArray(task.value.timeSpec.cronTime)) {
			task.value.timeSpec.cronTime = task.value.timeSpec.cronTime.join(' ').trim();
		}
		setTaskType();
	} else {
		task.value = {...defaultTask};
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
		30000,
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
		30000,
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
	const cronobj = cron(expression, {preset: SchedulerCron.cronTraits});
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
	const record = {...task.value};
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
	if (componentProps.action === FormAction.Edit && componentProps.schedulerTask?.taskId !== null) {
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
	task.value = {...defaultTask};
}
</script>
