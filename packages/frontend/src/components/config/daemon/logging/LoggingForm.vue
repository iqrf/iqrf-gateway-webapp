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
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
				container-type='card-title'
				:tooltip='$t("components.config.daemon.logging.actions.add")'
				:disabled='disabled'
			/>
			<IDataTableAction
				v-if='action === Action.Edit'
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.daemon.logging.actions.edit")'
				:disabled='disabled'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			validate-on='input'
			:disabled='componentState === ComponentState.Action'
			@submit.prevent='onSubmit()'
		>
			<ICard :action='action'>
				<template #title>
					{{ dialogTitle }}
				</template>
				<ITextInput
					v-model='profile.instance'
					:label='$t("components.config.daemon.logging.profile")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.logging.validation.profile.required")),
					]'
					required
				/>
				<ITextInput
					v-model='profile.path'
					:label='$t("components.config.daemon.logging.logDir")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.logging.validation.logDir.required")),
					]'
					required
				/>
				<ITextInput
					v-model='profile.filename'
					:label='$t("components.config.daemon.logging.filename")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.logging.validation.filename.required")),
					]'
					required
				/>
				<INumberInput
					v-model='profile.maxSizeMB'
					:label='$t("components.config.daemon.logging.maxSize")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.logging.validation.maxSize.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.logging.validation.maxSize.min")),
						(v: number) => ValidationRules.min(v, 1, $t("components.config.daemon.logging.validation.maxSize.min")),
					]'
					:min='1'
					required
				/>
				<v-checkbox
					v-model='profile.timestampFiles'
					:label='$t("components.config.daemon.logging.timestamps")'
					hide-details
				/>
				<INumberInput
					v-model='profile.maxAgeMinutes'
					:label='$t("components.config.daemon.logging.maxAge")'
					:description='$t("components.config.daemon.logging.notes.timestampsDisabled")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.logging.validation.maxAge.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.logging.validation.maxAge.integer")),
						(v: number) => ValidationRules.min(v, 0, $t("components.config.daemon.logging.validation.maxAge.min")),
					]'
					:min='0'
					:disabled='!profile.timestampFiles'
					required
				/>
				<INumberInput
					v-model='profile.maxNumber'
					:label='$t("components.config.daemon.logging.maxNumber")'
					:description='$t("components.config.daemon.logging.notes.timestampsDisabled")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.logging.validation.maxNumber.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.logging.validation.maxNumber.integer")),
						(v: number) => ValidationRules.min(v, 0, $t("components.config.daemon.logging.validation.maxNumber.min")),
					]'
					:min='0'
					:disabled='!profile.timestampFiles'
					required
				/>
				<IDataTable
					:headers='headers'
					:items='profile.VerbosityLevels'
					:dense='true'
					:hover='true'
					no-data-text='components.config.daemon.logging.channels.noChannels'
				>
					<template #top>
						<v-toolbar density='compact' rounded>
							<v-toolbar-title>
								{{ $t("components.config.daemon.logging.channels.title") }}
							</v-toolbar-title>
							<v-toolbar-items>
								<LoggingVerbosityForm
									:action='Action.Add'
									:disabled='componentState === ComponentState.Action'
									@save='saveLevel'
								/>
								<IActionBtn
									:action='Action.Delete'
									container-type='card-title'
									:tooltip='$t("components.config.daemon.logging.channels.actions.deleteAll")'
									:disabled='componentState === ComponentState.Action'
									@click='clearLevels()'
								/>
							</v-toolbar-items>
						</v-toolbar>
					</template>
					<template #item.level='{ item }'>
						{{ severityLabel(item.level) }}
					</template>
					<template #item.actions='{ item, index }'>
						<LoggingVerbosityForm
							:action='Action.Edit'
							:index='index'
							:logging-level='item'
							:disabled='componentState === ComponentState.Action'
							@save='saveLevel'
						/>
						<IDataTableAction
							:action='Action.Delete'
							:tooltip='$t("components.config.daemon.logging.channels.actions.delete")'
							:disabled='componentState === ComponentState.Action'
							@click='removeLevel(index)'
						/>
					</template>
				</IDataTable>
				<template #actions>
					<IActionBtn
						:action='action'
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
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type ShapeTraceChannelVerbosity,
	type ShapeTraceFileService,
	ShapeTraceVerbosity,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
	IDataTableAction,
	IModalWindow,
	INumberInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { computed, type PropType, ref, type Ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import LoggingVerbosityForm from '@/components/config/daemon/logging/LoggingVerbosityForm.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const emit = defineEmits(['saved']);
const componentProps = defineProps({
	action: {
		type: String as PropType<Action>,
		default: Action.Add,
		required: false,
	},
	loggingProfile: {
		type: Object as PropType<ShapeTraceFileService>,
		default: () => ({
			component: IqrfGatewayDaemonComponentName.ShapeTraceFile,
			instance: '',
			path: '/var/log/iqrf-gateway-daemon',
			filename: '',
			maxSizeMB: 1_048_576,
			maxNumber: 0,
			maxAgeMinutes: 0,
			timestampFiles: false,
			VerbosityLevels: [
				{ channel: 0, level: ShapeTraceVerbosity.Info },
			],
		}),
		required: false,
	},
	disabled: {
		type: Boolean,
		default: false,
		required: false,
	},
});
const i18n = useI18n();
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
const defaultProfile: ShapeTraceFileService = {
	component: IqrfGatewayDaemonComponentName.ShapeTraceFile,
	instance: '',
	path: '/var/log/iqrf-gateway-daemon',
	filename: '',
	maxSizeMB: 1_048_576,
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
	{ key: 'channel', title: i18n.t('components.config.daemon.logging.channel') },
	{ key: 'level', title: i18n.t('components.config.daemon.logging.severity') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end' },
];
const dialogTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.config.daemon.logging.actions.add').toString();
	}
	return i18n.t('components.config.daemon.logging.actions.edit').toString();
});

watch(show, (newVal: boolean): void => {
	if (!newVal) {
		return;
	}
	if (componentProps.action === Action.Edit && componentProps.loggingProfile) {
		profile.value = structuredClone(componentProps.loggingProfile);
		if (profile.value.maxAgeMinutes === undefined) {
			profile.value.maxAgeMinutes = 0;
		}
		if (profile.value.maxNumber === undefined) {
			profile.value.maxNumber = 0;
		}
		instance = componentProps.loggingProfile.instance;
	} else {
		profile.value = structuredClone(defaultProfile);
		instance = defaultProfile.instance;
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...profile.value };
	try {
		if (componentProps.action === Action.Add) {
			await service.createInstance(IqrfGatewayDaemonComponentName.ShapeTraceFile, params);
		} else {
			await service.updateInstance(IqrfGatewayDaemonComponentName.ShapeTraceFile, instance, params);
		}
		toast.success(
			i18n.t('components.config.daemon.logging.messages.save.success', { name: params.instance }),
		);
		close();
		emit('saved');
	} catch {
		toast.error(
			i18n.t('components.config.daemon.logging.messages.save.failed', { name: params.instance }),
		);
	}
	componentState.value = ComponentState.Ready;
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
	profile.value = structuredClone(defaultProfile);
}
</script>
