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
			<v-btn
				v-if='action === Action.Add'
				v-bind='props'
				color='success'
				:icon='mdiPlus'
			/>
			<v-icon
				v-else
				v-bind='props'
				color='info'
				class='me-2'
				:icon='mdiPencil'
				size='large'
			/>
		</template>
		<v-form
			v-slot='{ isValid }'
			ref='form'
			@submit.prevent='onSubmit'
		>
			<Card
				:header-color='action === Action.Add ? "success" : "primary"'
			>
				<template #title>
					{{ $t(`components.config.daemon.scheduler.task.actions.${action}`) }}
				</template>
				<v-textarea
					v-model='message'
					:label='$t("components.config.daemon.scheduler.task.message")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.scheduler.validation.messageMissing")),
						(v: string) => ValidationRules.json(v, $t("components.config.daemon.scheduler.validation.messageInvalid")),
						(v: string) => isDaemonApiRequest(JSON.parse(v)) || $t("components.config.daemon.scheduler.validation.messageInvalid"),
					]'
					auto-grow
					clearable
					rows='1'
					required
				/>
				<SelectInput
					v-model='selected'
					:items='messagingOptions'
					:label='$t("components.config.daemon.scheduler.task.messaging")'
					multiple
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
import {
	type DaemonApiRequest,
	type MessagingInstance,
	type SchedulerRecordTask,
} from '@iqrf/iqrf-gateway-daemon-utils/types';
import { type IqrfGatewayDaemonSchedulerMessagings } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiPencil, mdiPlus } from '@mdi/js';
import { computed, type PropType, ref , type Ref, watchEffect } from 'vue';
import { VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { Action } from '@/types/Action';

const componentProps = defineProps({
	action: {
		type: String as PropType<Action>,
		default: Action.Add,
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
const form: Ref<VForm | null> = ref(null);
const message: Ref<string | null> = ref(null);
const selected: Ref<string[] | MessagingInstance[]> = ref([]);

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

watchEffect((): void => {
	if (componentProps.action === Action.Edit && componentProps.task !== null) {
		message.value = JSON.stringify(componentProps.task.message, null, 4);
		selected.value = componentProps.task.messaging;
	}
});

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
