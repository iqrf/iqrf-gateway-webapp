<template>
	<v-dialog
		v-model='show'
		scrollable
		persistent
		no-click-animation
		:width='width'
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
				<TextInput
					v-model.number='port'
					type='number'
					:label='$t("common.labels.port")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("common.validation.portMissing")),
						(v: number) => ValidationRules.integer(v, $t("common.validation.portInvalid")),
						(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.portInvalid")),
					]'
					required
				/>
				<TextInput
					v-if='[MqttProtocol.WS, MqttProtocol.WSS].includes(protocol)'
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
	</v-dialog>
</template>

<script lang='ts' setup>
import { type Ref, ref } from 'vue';
import { watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';

import Card from '@/components/Card.vue';
import SelectInput from '@/components/SelectInput.vue';
import TextInput from '@/components/TextInput.vue';
import { MqttProtocol } from '@/enums/mqtt';
import { getModalWidth } from '@/helpers/modal';
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
const width = getModalWidth();
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
const port: Ref<number> = ref(1883);
const path: Ref<string> = ref('');

watchEffect(async (): Promise<void> => {
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

async function onSubmit(): Promise<void> {
	let url = `${protocol.value}://${hostname.value}:${port.value}`;
	if (protocol.value === MqttProtocol.WS || protocol.value === MqttProtocol.WSS) {
		url += '/' + path.value;
	}
	emit('edited', url);
}

function close(): void {
	show.value = false;
}

</script>
