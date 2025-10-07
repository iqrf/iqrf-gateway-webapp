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
			<v-btn
				v-bind='props'
				color='primary'
			>
				{{ $t("common.buttons.edit") }}
			</v-btn>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			@submit.prevent='onSubmit()'
		>
			<ICard>
				<template #title>
					{{ cardTitle }}
				</template>
				<ISelectInput
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
				<INumberInput
					v-model='port'
					:label='$t("common.labels.port")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("common.validation.port.required")),
						(v: number) => ValidationRules.integer(v, $t("common.validation.port.integer")),
						(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.port.between")),
					]'
					:min='1'
					:max='65535'
					required
				/>
				<ITextInput
					v-model='path'
					:label='$t("common.labels.path")'
				/>
				<template #actions>
					<IActionBtn
						:action='Action.Save'
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
import { Action, IActionBtn, ICard, IModalWindow, INumberInput, ISelectInput, ITextInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { ref, type Ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

import { WebsocketProtocol } from '@/enums/websocket';

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
const regexCapture = new RegExp(/(?<protocol>ws|wss):\/\/(?<host>.+):(?<port>\d+)(\/(?<path>.*))?/);
const protocol: Ref<WebsocketProtocol> = ref(WebsocketProtocol.WS);
const protocolOptions = [
	{
		title: i18n.t('common.labels.protocols.ws'),
		value: WebsocketProtocol.WS,
	},
	{
		title: i18n.t('common.labels.protocols.wss'),
		value: WebsocketProtocol.WSS,
	},
];
const hostname: Ref<string> = ref('');
const port: Ref<number> = ref(80);
const path: Ref<string> = ref('');

watch(show, (newVal: boolean): void => {
	if (newVal) {
		const parsed = componentProps.url.match(regexCapture);
		if (parsed?.groups === undefined) {
			return;
		}
		protocol.value = parsed.groups.protocol as WebsocketProtocol;
		hostname.value = parsed.groups.host;
		const portVal = Number.parseInt(parsed.groups.port);
		if (!Number.isNaN(portVal)) {
			port.value = portVal;
		}
		path.value = parsed.groups.path ?? '';
	}
});

async function onSubmit(): Promise<void> {
	const url = `${protocol.value}://${hostname.value}:${port.value}/${path.value}`;
	emit('edited', url);
	close();
}

function close(): void {
	show.value = false;
}

</script>
