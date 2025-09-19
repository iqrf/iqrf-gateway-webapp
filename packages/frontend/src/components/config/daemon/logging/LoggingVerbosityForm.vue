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
			<v-btn
				v-if='action === Action.Add'
				v-tooltip:bottom='$t("components.config.daemon.logging.channels.actions.add")'
				v-bind='props'
				color='success'
				:icon='mdiPlus'
			/>
			<IDataTableAction
				v-else
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.daemon.logging.channels.actions.edit")'
			/>
		</template>
		<v-form ref='form' v-slot='{ isValid }' @submit.prevent='onSubmit()'>
			<ICard :action='action'>
				<template #title>
					{{ $t(`components.config.daemon.logging.channels.actions.${action}`) }}
				</template>
				<NumberInput
					v-model.number='level.channel'
					:label='$t("components.config.daemon.logging.channel")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.logging.validation.channelMissing")),
						(v: number) => ValidationRules.integer(v, $t("components.config.daemon.logging.validation.channelInvalid")),
						(v: number) => ValidationRules.min(v, 0, $t("components.config.daemon.logging.validation.channelInvalid")),
					]'
					required
				/>
				<SelectInput
					v-model='level.level'
					:label='$t("components.config.daemon.logging.severity")'
					:items='severityOptions'
				/>
				<template #actions>
					<IActionBtn
						:action='action'
						container-type='card'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						container-type='card'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { type ShapeTraceChannelVerbosity, ShapeTraceVerbosity } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	IActionBtn,
	ICard,
	IDataTableAction,
	IModalWindow,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiPlus } from '@mdi/js';
import { type PropType, ref, type Ref, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { VForm } from 'vuetify/components';

import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import { validateForm } from '@/helpers/validateForm';

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
const form: Ref<VForm | null> = ref(null);
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

watchEffect((): void => {
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
