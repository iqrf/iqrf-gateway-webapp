<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
			<v-btn
				v-if='action === Action.Add'
				v-tooltip:bottom='$t("components.configuration.daemon.logging.channels.actions.add")'
				v-bind='props'
				color='success'
				:icon='mdiPlus'
			/>
			<DataTableAction
				v-else
				v-bind='props'
				:action='action'
				:tooltip='$t("components.configuration.daemon.logging.channels.actions.edit")'
			/>
		</template>
		<v-form ref='form' v-slot='{ isValid }' @submit.prevent='onSubmit'>
			<Card :action='action'>
				<template #title>
					{{ $t(`components.configuration.daemon.logging.channels.actions.${action}`) }}
				</template>
				<NumberInput
					v-model.number='level.channel'
					:label='$t("components.configuration.daemon.logging.channel")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.logging.validation.channelMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.logging.validation.channelInvalid")),
						(v: number) => ValidationRules.min(v, 0, $t("components.configuration.daemon.logging.validation.channelInvalid")),
					]'
					required
				/>
				<SelectInput
					v-model='level.level'
					:label='$t("components.configuration.daemon.logging.severity")'
					:items='severityOptions'
				/>
				<template #actions>
					<CardActionBtn
						:action='action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<CardActionBtn
						:action='Action.Cancel'
						@click='close'
					/>
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { ShapeTraceVerbosity, type ShapeTraceChannelVerbosity } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiPlus } from '@mdi/js';
import { ref, type Ref, watchEffect, type PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import { VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { Action } from '@/types/Action';

const emit = defineEmits(['save']);
const componentProps = defineProps({
	action: {
		type: String as PropType<Action>,
		required: false,
		default: Action.Add,
	},
	index: {
		type: [Number, null] as PropType<number|null>,
		required: false,
		default: null,
	},
	loggingLevel: {
		type: Object as PropType<ShapeTraceChannelVerbosity>,
		required: false,
		default: () => ({
			channel: 0,
			level: ShapeTraceVerbosity.Info,
		}),
	},
});
const show: Ref<boolean> = ref(false);
const i18n = useI18n();
const defaultLevel: ShapeTraceChannelVerbosity = {
	channel: 0,
	level: ShapeTraceVerbosity.Info,
};
const form: Ref<typeof VForm | null> = ref(null);
const level: Ref<ShapeTraceChannelVerbosity> = ref({ ...defaultLevel });
const severityOptions = [
	{
		title: i18n.t('common.labels.severity.debug'),
		value: ShapeTraceVerbosity.Debug,
	},
	{
		title: i18n.t('common.labels.severity.info'),
		value: ShapeTraceVerbosity.Info,
	},
	{
		title: i18n.t('common.labels.severity.warning'),
		value: ShapeTraceVerbosity.Warning,
	},
	{
		title: i18n.t('common.labels.severity.error'),
		value: ShapeTraceVerbosity.Error,
	},
];

watchEffect(async (): Promise<void> => {
	if (componentProps.action === Action.Edit && componentProps.loggingLevel) {
		level.value = { ...componentProps.loggingLevel };
	} else {
		level.value = { ...defaultLevel };
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	close();
	emit('save', componentProps.index, level.value);
}

function close(): void {
	show.value = false;
}
</script>
