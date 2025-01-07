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
				:tooltip='$t("components.config.daemon.logging.actions.add")'
			/>
			<DataTableAction
				v-if='action === Action.Edit'
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.daemon.logging.actions.edit")'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			validate-on='input'
			:disabled='componentState === ComponentState.Saving'
			@submit.prevent='onSubmit'
		>
			<Card :action='action'>
				<template #title>
					{{ dialogTitle }}
				</template>
				<TextInput
					v-model='profile.instance'
					:label='$t("components.config.daemon.logging.profile")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.logging.validations.profile.required")),
					]'
					required
				/>
				<TextInput
					v-model='profile.path'
					:label='$t("components.config.daemon.logging.logDir")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.logging.validations.logDir.required")),
					]'
					required
				/>
				<TextInput
					v-model='profile.filename'
					:label='$t("components.config.daemon.logging.filename")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.logging.validations.filename.required")),
					]'
					required
				/>
				<NumberInput
					v-model.number='profile.maxSizeMB'
					:label='$t("components.config.daemon.logging.maxSize")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.logging.validations.maxSize.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.logging.validations.maxSize.min")),
						(v: number) => ValidationRules.min(v, 1, $t("components.config.daemon.logging.validations.maxSize.min")),
					]'
					required
				/>
				<v-checkbox
					v-model='profile.timestampFiles'
					:label='$t("components.config.daemon.logging.timestamps")'
				/>
				<NumberInput
					v-model.number='profile.maxAgeMinutes'
					:label='$t("components.config.daemon.logging.maxAge")'
					:description='$t("components.config.daemon.logging.notes.timestampsDisabled")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.logging.validation.maxAgeMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.logging.validation.maxAgeInvalid")),
						(v: number) => ValidationRules.min(v, 0, $t("components.config.daemon.logging.validation.maxAgeInvalid")),
					]'
					:disabled='!profile.timestampFiles'
					required
				/>
				<NumberInput
					v-model.number='profile.maxNumber'
					:label='$t("components.config.daemon.logging.maxNumber")'
					:description='$t("components.config.daemon.logging.notes.timestampsDisabled")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.logging.validation.maxNumberMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.logging.validation.maxNumberInvalid")),
						(v: number) => ValidationRules.min(v, 0, $t("components.config.daemon.logging.validation.maxNumberInvalid")),
					]'
					:disabled='!profile.timestampFiles'
					required
				/>
				<DataTable
					:headers='headers'
					:items='profile.VerbosityLevels'
					:dense='true'
					:hover='true'
					:no-data-text='$t("components.config.daemon.logging.channels.noChannels")'
				>
					<template #top>
						<v-toolbar density='compact' rounded>
							<v-toolbar-title>
								{{ $t("components.config.daemon.logging.channels.title") }}
							</v-toolbar-title>
							<v-toolbar-items>
								<LoggingVerbosityForm
									:action='Action.Add'
									@save='saveLevel'
								/>
								<v-btn
									v-tooltip:bottom='$t("components.config.daemon.logging.channels.actions.deleteAll")'
									color='red'
									:icon='mdiDelete'
									@click='clearLevels'
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
							@save='saveLevel'
						/>
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
							{{ $t('components.config.daemon.logging.channels.actions.delete') }}
						</v-tooltip>
					</template>
				</DataTable>
				<template #actions>
					<CardActionBtn
						:action='action'
						:disabled='!isValid.value || componentState === ComponentState.Saving'
						type='submit'
					/>
					<v-spacer />
					<CardActionBtn
						:action='Action.Cancel'
						:disabled='componentState === ComponentState.Saving'
						@click='close'
					/>
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type ShapeTraceChannelVerbosity,
	type ShapeTraceFileService,
	ShapeTraceVerbosity,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiDelete } from '@mdi/js';
import { computed, type PropType, ref, type Ref , watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import LoggingVerbosityForm from '@/components/config/daemon/logging/LoggingVerbosityForm.vue';
import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

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

watchEffect((): void => {
	if (componentProps.action === Action.Edit && componentProps.loggingProfile) {
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
	if (componentProps.action === Action.Add) {
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
		i18n.t('components.config.daemon.logging.messages.save.success', { name: name }),
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
