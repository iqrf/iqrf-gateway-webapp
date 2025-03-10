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
				v-bind='props'
				color='primary'
			>
				{{ $t("common.buttons.edit") }}
			</v-btn>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			@submit.prevent='onSubmit'
		>
			<Card>
				<template #title>
					{{ cardTitle }}
				</template>
				<SelectInput
					v-model='protocol'
					:label='$t("common.labels.protocol")'
					:items='protocolOptions'
				/>
				<TextInput
					v-model='hostname'
					:label='$t("common.labels.hostname")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("common.validation.hostnameMissing")),
						(v: string) => ValidationRules.server(v, $t("common.validation.hostnameInvalid")),
					]'
					required
				/>
				<NumberInput
					v-model.number='port'
					:label='$t("common.labels.port")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("common.validation.portMissing")),
						(v: number) => ValidationRules.integer(v, $t("common.validation.portInvalid")),
						(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.portInvalid")),
					]'
					required
				/>
				<TextInput
					v-model='path'
					:label='$t("common.labels.path")'
				/>
				<template #actions>
					<v-btn
						color='primary'
						type='submit'
						variant='elevated'
						:disabled='!isValid.value'
					>
						{{ $t('common.buttons.save') }}
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
import { ref, type Ref } from 'vue';
import { watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';

import Card from '@/components/layout/card/Card.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { WebsocketProtocol } from '@/enums/websocket';
import ValidationRules from '@/helpers/ValidationRules';


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

watchEffect((): void => {
	if (show.value) {
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
