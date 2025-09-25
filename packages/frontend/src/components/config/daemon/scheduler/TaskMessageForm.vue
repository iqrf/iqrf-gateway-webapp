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
			<IActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='Action.Add'
				container-type='card-title'
				:tooltip='$t("components.config.daemon.scheduler.task.actions.add")'
				:disabled='disabled'
			/>
			<IDataTableAction
				v-else
				v-bind='props'
				:action='Action.Edit'
				:tooltip='$t("components.config.daemon.scheduler.task.actions.edit")'
				:disabled='disabled'
			/>
		</template>
		<v-form
			v-slot='{ isValid }'
			ref='form'
			@submit.prevent='onSubmit()'
		>
			<ICard :action='action'>
				<template #title>
					{{ $t(`components.config.daemon.scheduler.task.actions.${action}`) }}
				</template>
				<v-textarea
					v-model='message'
					:label='$t("components.config.daemon.scheduler.task.message")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.scheduler.validation.message.required")),
						(v: string) => ValidationRules.json(v, $t("components.config.daemon.scheduler.validation.message.json")),
						(v: string) => isDaemonApiRequest(JSON.parse(v)) || $t("components.config.daemon.scheduler.validation.message.json"),
					]'
					auto-grow
					clearable
					rows='1'
					required
				/>
				<ISelectInput
					v-model='selected'
					:items='messagingOptions'
					:label='$t("components.config.daemon.scheduler.task.messaging")'
					multiple
				/>
				<template #actions>
					<IActionBtn
						:action='action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { MessagingType } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import {
	type MessagingInstance,
	type TApiResponse,
} from '@iqrf/iqrf-gateway-daemon-utils/types';
import { SchedulerTask } from '@iqrf/iqrf-gateway-daemon-utils/types/management';
import { type IqrfGatewayDaemonMessagingInstances } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	IActionBtn,
	ICard,
	IDataTableAction,
	IModalWindow,
	ISelectInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { computed, type PropType, ref , type Ref, watch } from 'vue';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';

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
		type: [Object, null] as PropType<SchedulerTask | null>,
		default: null,
		required: false,
	},
	messagings: {
		type: [Object, null] as PropType<IqrfGatewayDaemonMessagingInstances | null>,
		default: null,
		required: false,
	},
	disabled: {
		type: Boolean,
		default: false,
		required: false,
	},
});
const emit = defineEmits(['save']);
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
const message: Ref<string | null> = ref(null);
const selected: Ref<MessagingInstance[]> = ref([]);

const messagingOptions = computed(() => {
	if (componentProps.messagings === null) {
		return [];
	}
	const mqttOptions = componentProps.messagings.mqtt.map((item: string) => ({
		title: `[MQTT] ${item}`,
		value: {
			type: MessagingType.Mqtt,
			instance: item,
		},
	}));
	const wsOptions = componentProps.messagings.ws.map((item: string) => ({
		title: `[WS] ${item}`,
		value: {
			type: MessagingType.Websocket,
			instance: item,
		},
	}));
	return [
		...mqttOptions,
		...wsOptions,
	];
});

watch(show, (newVal: boolean): void => {
	if (!newVal) {
		return;
	}
	if (componentProps.action === Action.Edit && componentProps.task !== null) {
		message.value = JSON.stringify(componentProps.task.message, null, 4);
		selected.value = componentProps.task.messaging;
	}
});

function isDaemonApiRequest(value: object): value is TApiResponse {
	if (!('mType' in value) || !('data' in value)) {
		return false;
	}
	return true;
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || message.value === null) {
		return;
	}
	const recordTask: SchedulerTask = {
		message: JSON.parse(message.value),
		messaging: selected.value,
	};
	close();
	emit('save', componentProps.index, recordTask);
}

function close(): void {
	show.value = false;
}
</script>
