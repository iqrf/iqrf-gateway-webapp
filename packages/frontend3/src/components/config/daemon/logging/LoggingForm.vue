<template>
	<ModalWindow v-model='show'>
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
				{{ $t('components.configuration.daemon.logging.actions.add') }}
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
			validate-on='input'
			:disabled='componentState === ComponentState.Saving'
			@submit.prevent='onSubmit'
		>
			<Card>
				<template #title>
					{{ dialogTitle }}
				</template>
				<TextInput
					v-model='profile.instance'
					:label='$t("components.configuration.daemon.logging.profile")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.logging.validation.profileMissing")),
					]'
					required
				/>
				<TextInput
					v-model='profile.path'
					:label='$t("components.configuration.daemon.logging.logDir")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.logging.validation.logDirMissing")),
					]'
					required
				/>
				<TextInput
					v-model='profile.filename'
					:label='$t("components.configuration.daemon.logging.filename")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.logging.validation.filenameMissing")),
					]'
					required
				/>
				<TextInput
					v-model.number='profile.maxSizeMB'
					type='number'
					:label='$t("components.configuration.daemon.logging.maxSize")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.logging.validation.maxSizeMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.logging.validation.maxSizeInvalid")),
						(v: number) => ValidationRules.min(v, 1, $t("components.configuration.daemon.logging.validation.maxSizeInvalid")),
					]'
					required
				/>
				<v-checkbox
					v-model='profile.timestampFiles'
					:label='$t("components.configuration.daemon.logging.timestamps")'
					density='compact'
					hide-details
				/>
				<TextInput
					v-model.number='profile.maxAgeMinutes'
					type='number'
					:label='$t("components.configuration.daemon.logging.maxAge")'
					:description='$t("components.configuration.daemon.logging.notes.timestampsDisabled")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.logging.validation.maxAgeMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.logging.validation.maxAgeInvalid")),
						(v: number) => ValidationRules.min(v, 0, $t("components.configuration.daemon.logging.validation.maxAgeInvalid")),
					]'
					:disabled='!profile.timestampFiles'
					required
				/>
				<TextInput
					v-model.number='profile.maxNumber'
					type='number'
					:label='$t("components.configuration.daemon.logging.maxNumber")'
					:description='$t("components.configuration.daemon.logging.notes.timestampsDisabled")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.logging.validation.maxNumberMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.logging.validation.maxNumberInvalid")),
						(v: number) => ValidationRules.min(v, 0, $t("components.configuration.daemon.logging.validation.maxNumberInvalid")),
					]'
					:disabled='!profile.timestampFiles'
					required
				/>
				<DataTable
					:headers='headers'
					:items='profile.VerbosityLevels'
					:dense='true'
					:hover='true'
					:no-data-text='$t("components.configuration.daemon.logging.channels.noChannels")'
				>
					<template #top>
						<v-toolbar density='compact' rounded>
							<v-toolbar-title>
								{{ $t("components.configuration.daemon.logging.channels.title") }}
							</v-toolbar-title>
							<v-toolbar-items>
								<LoggingVerbosityForm
									:action='FormAction.Add'
									@save='saveLevel'
								/>
								<v-tooltip
									location='bottom'
								>
									<template #activator='{ props }'>
										<v-btn
											v-bind='props'
											color='error'
											:icon='mdiDelete'
											@click='clearLevels'
										/>
									</template>
									{{ $t('components.configuration.daemon.logging.channels.actions.deleteAll') }}
								</v-tooltip>
							</v-toolbar-items>
						</v-toolbar>
					</template>
					<template #item.level='{ item }'>
						{{ severityLabel(item.level) }}
					</template>
					<template #item.actions='{ item, index }'>
						<span>
							<LoggingVerbosityForm
								:action='FormAction.Edit'
								:index='index'
								:logging-level='item'
								@save='saveLevel'
							/>
							<v-tooltip
								activator='parent'
								location='bottom'
							>
								{{ $t('components.configuration.daemon.logging.channels.actions.edit') }}
							</v-tooltip>
						</span>
						<v-tooltip
							location='bottom'
						>
							<template #activator='{ props }'>
								<v-icon
									v-bind='props'
									color='error'
									size='large'
									@click='removeLevel(index)'
								>
									{{ mdiDelete }}
								</v-icon>
							</template>
							{{ $t('components.configuration.daemon.logging.channels.actions.delete') }}
						</v-tooltip>
					</template>
				</DataTable>
				<template #actions>
					<v-btn
						color='primary'
						type='submit'
						variant='elevated'
						:disabled='!isValid.value || componentState === ComponentState.Saving'
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
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	ShapeTraceVerbosity,
	type ShapeTraceFileService,
	type ShapeTraceChannelVerbosity,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiDelete, mdiPencil, mdiPlus } from '@mdi/js';
import { computed, type PropType, type Ref, ref , watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import LoggingVerbosityForm from '@/components/config/daemon/logging/LoggingVerbosityForm.vue';
import DataTable from '@/components/DataTable.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import TextInput from '@/components/TextInput.vue';
import { FormAction } from '@/enums/controls';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const emit = defineEmits(['saved']);
const componentProps = defineProps({
	action: {
		type: String as PropType<FormAction>,
		default: FormAction.Add,
		required: false,
	},
	loggingProfile: {
		type: Object as PropType<ShapeTraceFileService>,
		default: () => ({
			component: IqrfGatewayDaemonComponentName.ShapeTraceFile,
			instance: '',
			path: '/var/log/iqrf-gateway-daemon',
			filename: '',
			maxSizeMB: 1048576,
			maxNumber: 0,
			maxAgeMinutes: 0,
			timestampFiles: false,
			VerbosityLevels: [
				{ channel: 0, level: ShapeTraceVerbosity.Info },
			],
		}),
		required: false,
	},
});
const i18n = useI18n();
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const show: Ref<boolean> = ref(false);
const form: Ref<typeof VForm | null> = ref(null);
const defaultProfile: ShapeTraceFileService = {
	component: IqrfGatewayDaemonComponentName.ShapeTraceFile,
	instance: '',
	path: '/var/log/iqrf-gateway-daemon',
	filename: '',
	maxSizeMB: 1048576,
	maxNumber: 0,
	maxAgeMinutes: 0,
	timestampFiles: false,
	VerbosityLevels: [
		{ channel: 0, level: ShapeTraceVerbosity.Info },
	],
};
const profile: Ref<ShapeTraceFileService> = ref({ ...defaultProfile });
let instance = '';
const headers = [
	{ key: 'channel', title: i18n.t('components.configuration.daemon.logging.channel') },
	{ key: 'level', title: i18n.t('components.configuration.daemon.logging.severity') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end' },
];
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
		return i18n.t('components.configuration.daemon.logging.actions.add').toString();
	}
	return i18n.t('components.configuration.daemon.logging.actions.edit').toString();
});

watchEffect(async(): Promise<void> => {
	if (componentProps.action === FormAction.Edit && componentProps.loggingProfile) {
		profile.value = { ...componentProps.loggingProfile };
		if (profile.value.maxAgeMinutes === undefined) {
			profile.value.maxAgeMinutes = 0;
		}
		if (profile.value.maxNumber === undefined) {
			profile.value.maxNumber = 0;
		}
		instance = componentProps.loggingProfile.instance;
	} else {
		profile.value = { ...defaultProfile };
		instance = defaultProfile.instance;
	}
	componentState.value = ComponentState.Ready;
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const params = { ...profile.value };
	if (componentProps.action === FormAction.Add) {
		service.createInstance(IqrfGatewayDaemonComponentName.ShapeTraceFile, params)
			.then(() => handleSuccess(params.instance))
			.catch(handleError);
	} else {
		service.updateInstance(IqrfGatewayDaemonComponentName.ShapeTraceFile, instance, params)
			.then(() => handleSuccess(instance))
			.catch(handleError);
	}
}

function handleSuccess(name: string): void {
	componentState.value = ComponentState.Ready;
	toast.success(
		i18n.t('components.configuration.daemon.logging.messages.save.success', { name: name }),
	);
	close();
	emit('saved');
}

function handleError(): void {
	componentState.value = ComponentState.Ready;
	toast.error('TODO ERROR HANDLING');
}

function severityLabel(severity: ShapeTraceVerbosity): string {
	if (severity === ShapeTraceVerbosity.Debug) {
		return i18n.t('common.labels.severity.debug');
	} else if (severity === ShapeTraceVerbosity.Info) {
		return i18n.t('common.labels.severity.info');
	} else if (severity === ShapeTraceVerbosity.Warning) {
		return i18n.t('common.labels.severity.warning');
	}
	return i18n.t('common.labels.severity.error');
}

function saveLevel(index: number|null, level: ShapeTraceChannelVerbosity) {
	if (index === null) {
		profile.value.VerbosityLevels.push(level);
	} else {
		profile.value.VerbosityLevels[index] = level;
	}
}

function removeLevel(index: number): void {
	profile.value.VerbosityLevels.splice(index, 1);
}

function clearLevels(): void {
	profile.value.VerbosityLevels = [];
}

function close(): void {
	show.value = false;
	profile.value = { ...defaultProfile };
}
</script>
