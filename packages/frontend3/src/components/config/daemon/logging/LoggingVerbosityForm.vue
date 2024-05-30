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
				v-if='action === FormAction.Add'
				id='channel-add-activator'
				v-bind='props'
				:color='iconColor()'
				:icon='activatorIcon()'
			/>
			<v-tooltip
				v-if='action === FormAction.Add'
				activator='#channel-add-activator'
				location='bottom'
			>
				{{ $t('components.configuration.daemon.logging.channels.actions.add') }}
			</v-tooltip>
			<v-icon
				v-else
				v-bind='props'
				:color='iconColor()'
				class='me-2'
				size='large'
			>
				{{ activatorIcon() }}
			</v-icon>
		</template>
		<v-form ref='form' @submit.prevent='onSubmit'>
			<Card>
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
					<v-btn
						color='primary'
						type='submit'
						variant='elevated'
					>
						{{ $t(`common.buttons.${action}`) }}
					</v-btn>
					<v-spacer />
					<v-btn
						color='grey-darken-2'
						variant='elevated'
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
import { ShapeTraceVerbosity, type ShapeTraceChannelVerbosity } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiPencil, mdiPlus } from '@mdi/js';
import { ref, type Ref, watchEffect, type PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { FormAction } from '@/enums/controls';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';

const emit = defineEmits(['save']);
const componentProps = defineProps({
	action: {
		type: String as PropType<FormAction>,
		required: false,
		default: FormAction.Add,
	},
	index: {
		type: [Number, null] as PropType<Number|null>,
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
	if (componentProps.action === FormAction.Edit && componentProps.loggingLevel) {
		level.value = { ...componentProps.loggingLevel };
	} else {
		level.value = { ...defaultLevel };
	}
});

function activatorIcon(): string {
	if (componentProps.action === FormAction.Add) {
		return mdiPlus;
	}
	return mdiPencil;
}

function iconColor(): string {
	if (componentProps.action === FormAction.Add) {
		return 'black';
	}
	return 'info';
}

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
