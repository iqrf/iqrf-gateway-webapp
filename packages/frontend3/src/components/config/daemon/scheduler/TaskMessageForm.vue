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
				v-bind='props'
				:color='iconColor()'
				:icon='activatorIcon()'
			/>
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
		<v-form
			v-slot='{ isValid }'
			ref='form'
			@submit.prevent='onSubmit'
		>
			<Card>
				<template #title>
					{{ $t(`components.configuration.daemon.scheduler.task.actions.${action}`) }}
				</template>
				<v-textarea
					v-model='message'
					:label='$t("components.configuration.daemon.scheduler.task.message")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.scheduler.validation.messageMissing")),
						(v: string) => ValidationRules.json(v, $t("components.configuration.daemon.scheduler.validation.messageInvalid")),
						(v: string) => isDaemonApiRequest(JSON.parse(v)) || $t("components.configuration.daemon.scheduler.validation.messageInvalid"),
					]'
					auto-grow
					clearable
					rows='1'
					required
				/>
				<SelectInput
					v-model='selected'
					:items='messagingOptions'
					:label='$t("components.configuration.daemon.scheduler.task.messaging")'
					multiple
				/>
				<template #actions>
					<v-btn
						color='primary'
						type='submit'
						variant='elevated'
						:disabled='!isValid.value'
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
import { type DaemonApiRequest, type SchedulerRecordTask } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { type IqrfGatewayDaemonSchedulerMessagings } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiPencil, mdiPlus } from '@mdi/js';
import { ref, type Ref, watchEffect , type PropType, computed } from 'vue';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import SelectInput from '@/components/SelectInput.vue';
import { FormAction } from '@/enums/controls';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';

const componentProps = defineProps({
	action: {
		type: String as PropType<FormAction>,
		default: FormAction.Add,
		required: false,
	},
	index: {
		type: [Number, null] as PropType<number | null>,
		default: null,
		required: false,
	},
	task: {
		type: [Object, null] as PropType<SchedulerRecordTask | null>,
		default: null,
		required: false,
	},
	messagings: {
		type: [Object, null] as PropType<IqrfGatewayDaemonSchedulerMessagings | null>,
		default: null,
		required: false,
	},
});
const emit = defineEmits(['save']);
const show: Ref<boolean> = ref(false);
const form: Ref<typeof VForm | null> = ref(null);
const message: Ref<string | null> = ref(null);
const selected: Ref<string[]> = ref([]);

const messagingOptions = computed(() => {
	if (componentProps.messagings === null) {
		return [];
	}
	const mqttOptions = componentProps.messagings.mqtt.map((item: string) => ({
		title: `[MQTT] ${item}`,
		value: item,
	}));
	const wsOptions = componentProps.messagings.ws.map((item: string) => ({
		title: `[WS] ${item}`,
		value: item,
	}));
	return mqttOptions.concat(wsOptions);
});

watchEffect(async (): Promise<void> => {
	if (componentProps.action === FormAction.Edit && componentProps.task !== null) {
		message.value = JSON.stringify(componentProps.task.message, null, 4);
		selected.value = componentProps.task.messaging;
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

function isDaemonApiRequest(value: object): value is DaemonApiRequest {
	if (!('mType' in value) || !('data' in value)) {
		return false;
	}
	return true;
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || message.value === null) {
		return;
	}
	const recordTask: SchedulerRecordTask = {
		message: JSON.parse(message.value) as DaemonApiRequest,
		messaging: selected.value,
	};
	close();
	emit('save', componentProps.index, recordTask);
}

function close(): void {
	show.value = false;
}
</script>
