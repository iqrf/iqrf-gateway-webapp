<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

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
				:action='Action.Add'
				container-type='card-title'
				:tooltip='$t("components.accessControl.daemonAccessTokens.actions.add")'
				:disabled='disabled'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Action'
			@submit.prevent='onSubmit()'
		>
			<ICard :action='Action.Add'>
				<template #title>
					{{ $t('components.accessControl.daemonAccessTokens.actions.add') }}
				</template>
				<template #actions>
					<IActionBtn
						:action='Action.Add'
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
				<ITextInput
					v-model='owner'
					:label='$t("components.accessControl.daemonAccessTokens.owner")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.accessControl.daemonAccessTokens.validations.owner.required")),
					]'
					required
					:prepend-inner-icon='mdiAccount'
				/>
				<v-radio-group
					v-model='relativeExpiration'
				>
					<v-radio
						:label='$t("components.accessControl.daemonAccessTokens.relativeExpiration")'
						:value='true'
					/>
					<v-radio
						:label='$t("components.accessControl.daemonAccessTokens.absoluteExpiration")'
						:value='false'
					/>
				</v-radio-group>
				<div v-if='relativeExpiration'>
					<ISelectInput
						v-model='unit'
						:label='$t("components.accessControl.daemonAccessTokens.unit")'
						:items='units'
						:prepend-inner-icon='mdiClockTimeFour'
					/>
					<INumberInput
						v-model='unitCount'
						:label='$t("components.accessControl.daemonAccessTokens.count")'
						:rules='[
							(v: number|null) => ValidationRules.required(v, $t("components.config.journal.validation.unitCount.required")),
							(v: number) => ValidationRules.integer(v, $t("components.config.journal.validation.unitCount.integer")),
							(v: number) => ValidationRules.min(v, 1, $t("components.config.journal.validation.unitCount.min")),
						]'
						:min='1'
						:prepend-inner-icon='mdiNumeric1Box'
					/>
				</div>
				<IDateTimeInput
					v-else
					v-model='expiration'
					:label='$t("common.labels.expiration")'
					:min='toRaw(minDate)'
				/>
			</ICard>
		</v-form>
	</IModalWindow>
	<DaemonAccessTokenDisplayDialog
		ref='displayDialog'
	/>
</template>

<script lang='ts' setup>
import { DaemonApiTokenService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import { DaemonApiTokenCreate, DaemonApiTokenExpirationUnit } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { Action, ComponentState, IActionBtn, ICard, IDateTimeInput, IModalWindow, INumberInput, ISelectInput, ITextInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiAccount, mdiClockTimeFour, mdiNumeric1Box } from '@mdi/js';
import { DateTime } from 'luxon';
import { ref, toRaw, useTemplateRef, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import DaemonAccessTokenDisplayDialog from '@/components/access-control/daemon-access-tokens/DaemonAccessTokenDisplayDialog.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentProps = withDefaults(
	defineProps<{
		disabled?: boolean;
	}>(),
	{
		disabled: false,
	},
);
const emit = defineEmits<{
	refresh: [];
}>();
const componentState = ref<ComponentState>(ComponentState.Idle);
const i18n = useI18n();
const show = ref<boolean>(false);
const form = useTemplateRef<VForm>('form');
const displayDialog = useTemplateRef<InstanceType<typeof DaemonAccessTokenDisplayDialog>|null>('displayDialog');
const service: DaemonApiTokenService = useApiClient()
	.getSecurityServices()
	.getDaemonApiTokenService();
const owner = ref<string>('');
const relativeExpiration = ref<boolean>(true);
const expiration = ref<DateTime | null>(null);
const unit = ref<DaemonApiTokenExpirationUnit>(DaemonApiTokenExpirationUnit.Day);
const units = [
	{
		title: i18n.t('components.accessControl.daemonAccessTokens.units.day'),
		value: DaemonApiTokenExpirationUnit.Day,
	},
	{
		title: i18n.t('components.accessControl.daemonAccessTokens.units.week'),
		value: DaemonApiTokenExpirationUnit.Week,
	},
	{
		title: i18n.t('components.accessControl.daemonAccessTokens.units.month'),
		value: DaemonApiTokenExpirationUnit.Month,
	},
	{
		title: i18n.t('components.accessControl.daemonAccessTokens.units.year'),
		value: DaemonApiTokenExpirationUnit.Year,
	},
];
const unitCount = ref<number>(1);
const minDate = ref<DateTime | null>(null);

watch(show, (newVal: boolean): void => {
	if (!newVal) {
		return;
	}
	minDate.value = DateTime.now().plus({ hours: 1 });
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params: DaemonApiTokenCreate = relativeExpiration.value ? {
		owner: owner.value,
		unit: unit.value,
		count: unitCount.value,
	} : {
		owner: owner.value,
		expiration: expiration.value!.toISO({ precision: 'second' })!,
	};
	try {
		const token = await service.create(params);
		toast.success(
			i18n.t('components.accessControl.daemonAccessTokens.messages.create.success'),
		);
		close();
		emit('refresh');
		displayDialog.value?.open(token.token);
	} catch {
		toast.error(
			i18n.t('components.accessControl.daemonAccessTokens.messages.create.failed'),
		);
	}
	componentState.value = ComponentState.Idle;
}

function close(): void {
	show.value = false;
	resetForm();
}

function resetForm(): void {
	owner.value = '';
	relativeExpiration.value = true;
	expiration.value = null;
	unit.value = DaemonApiTokenExpirationUnit.Day;
	unitCount.value = 1;
}

</script>
