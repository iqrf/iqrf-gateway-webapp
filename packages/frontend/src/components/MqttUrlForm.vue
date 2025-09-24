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
	>
		<template #activator='{ props }'>
			<v-btn
				v-bind='props'
				color='primary'
				:prepend-icon='mdiPencil'
			>
				{{ $t("components.common.actions.edit") }}
			</v-btn>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			@submit.prevent='onSubmit'
		>
			<ICard>
				<template #title>
					{{ cardTitle }}
				</template>
				<SelectInput
					v-model='protocol'
					:label='$t("common.labels.protocol")'
					:items='protocolOptions'
				/>
				<ITextInput
					v-model='hostname'
					:label='$t("common.labels.hostname")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("common.validation.hostname.required")),
						(v: string) => ValidationRules.host(v, $t("common.validation.hostname.invalid")),
					]'
					required
				/>
				<NumberInput
					v-model.number='port'
					:label='$t("common.labels.port")'
					:min='1'
					:max='65535'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("common.validation.port.required")),
						(v: number) => ValidationRules.integer(v, $t("common.validation.port.integer")),
						(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.port.between")),
					]'
					required
				/>
				<ITextInput
					v-if='[MqttProtocol.WS, MqttProtocol.WSS].includes(protocol)'
					v-model='path'
					:label='$t("common.labels.path")'
				/>
				<template #actions>
					<IActionBtn
						:action='Action.Edit'
						container-type='card'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						container-type='card'
						@click='close'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import {
	Action,
	IActionBtn,
	ICard,
	IModalWindow,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiPencil } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';

import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import { MqttProtocol } from '@/enums/mqtt';

const componentProps = defineProps({
	cardTitle: {
		type: String,
		default: '',
		required: false,
	},
	url: {
		type: String,
		required: true,
	},
});
const emit = defineEmits(['edited']);
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const regexCapture = new RegExp(/(?<protocol>tcp|ssl|ws|wss|mqtt|mqtts):\/\/(?<host>.+):(?<port>\d+)(\/(?<path>.*))?/);
const protocol: Ref<MqttProtocol> = ref(MqttProtocol.TCP);
const protocolOptions = [
	{
		title: i18n.t('common.labels.protocols.tcp'),
		value: MqttProtocol.TCP,
	},
	{
		title: i18n.t('common.labels.protocols.ssl'),
		value: MqttProtocol.SSL,
	},
	{
		title: i18n.t('common.labels.protocols.ws'),
		value: MqttProtocol.WS,
	},
	{
		title: i18n.t('common.labels.protocols.wss'),
		value: MqttProtocol.WSS,
	},
];
const hostname: Ref<string> = ref('');
const port: Ref<number> = ref(1_883);
const path: Ref<string> = ref('');

watchEffect((): void => {
	if (show.value) {
		const parsed = componentProps.url.match(regexCapture);
		if (parsed?.groups === undefined) {
			return;
		}
		protocol.value = parsed.groups.protocol as MqttProtocol;
		hostname.value = parsed.groups.host;
		const portVal = Number.parseInt(parsed.groups.port);
		if (!Number.isNaN(portVal)) {
			port.value = portVal;
		}
		path.value = parsed.groups.path ?? '';
	}
});

function onSubmit(): void {
	let url = `${protocol.value}://${hostname.value}:${port.value}`;
	if (protocol.value === MqttProtocol.WS || protocol.value === MqttProtocol.WSS) {
		url += `/${path.value}`;
	}
	emit('edited', url);
	close();
}

function close(): void {
	show.value = false;
}

</script>
