<template>
	<Card>
		<template #title>
			{{ $t('pages.iqrfnet.send-json.title') }}
		</template>
		<template #titleActions>
			<v-btn
				size='small'
				href='https://docs.iqrf.org/iqrf-gateway/user/daemon/api.html'
				target='_blank'
			>
				{{ $t('components.iqrfnet.send-json.api') }}
			</v-btn>
		</template>
		<v-alert
			v-if='componentState === ComponentState.Saving'
			variant='tonal'
			color='info'
			:text='$t("components.iqrfnet.inProgress")'
			class='mb-2'
		/>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Saving'
		>
			<v-textarea
				v-model='json'
				:label='$t("components.iqrfnet.send-json.json")'
				auto-grow
				clearable
				rows='1'
				required
			/>
			<v-btn
				color='primary'
				:disabled='!isValid.value || componentState === ComponentState.Saving'
				@click='onSubmit'
			>
				{{ $t('common.buttons.send') }}
			</v-btn>
		</v-form>
	</Card>
	<RequestHistory
		class='mt-4'
		:messages='messages'
		@clear='clearMessages'
	/>
</template>

<script lang='ts' setup>
import {
	type DaemonApiResponse,
	DaemonMessageOptions,
	type DaemonApiRequest,
	type JsonMessage,
} from '@iqrf/iqrf-gateway-daemon-utils';
import { type Ref, ref } from 'vue';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import RequestHistory from '@/components/iqrfnet/send-json/RequestHistory.vue';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const daemonStore = useDaemonStore();
const form: Ref<typeof VForm | null> = ref(null);
const msgId: Ref<string | null> = ref(null);
const json: Ref<string | null> = ref(null);
const messages: Ref<JsonMessage[]> = ref([]);

daemonStore.$onAction(
	({ name, after }) => {
		if (name === 'onMessage') {
			after((rsp: DaemonApiResponse) => {
				if (rsp.data.msgId !== msgId.value) {
					return;
				}
				if (rsp.mType === 'iqmeshNetwork_AutoNetwork') {
					handleAutonetworkResponse(rsp);
				} else if (rsp.mType === 'iqmeshNetwork_Backup') {
					handleBackupResponse(rsp);
				} else if (rsp.mType === 'messageError') {
					handleMessageError(rsp);
				} else {
					daemonStore.removeMessage(msgId.value);
					handleResponse(rsp);
				}
			});
		}
	},
);

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || json.value === null) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const request: DaemonApiRequest = JSON.parse(json.value);
	const options = new DaemonMessageOptions(request, null, null, () => {
		msgId.value = null;
		componentState.value = ComponentState.Ready;
	});
	if (
		(request.data.req !== undefined && {}.hasOwnProperty.call(request.data.req, 'nAdr') && request.data.req.nAdr === 255) ||
		['iqrfEmbedOs_Batch', 'iqrfEmbedOs_SelectiveBatch'].includes(request.mType)
	) {
		options.timeout = 1000;
	} else if ([
		'iqmeshNetwork_AutoNetwork',
		'iqmeshNetwork_Backup',
		'iqmeshNetwork_OtaUpload',
		'iqrfEmbedCoordinator_Discovery',
	].includes(request.mType)) {
		options.timeout = null;
		options.message = null;
	} else {
		options.timeout = 60000;
	}
	daemonStore.sendMessage(options)
		.then((val: string) => {
			msgId.value = val;
			messages.value.unshift({
				msgId: val,
				mType: request.mType,
				timestamp: new Date().toLocaleString(),
				request: JSON.stringify(request, null, 4),
				response: [],
			});
		});
}

function handleAutonetworkResponse(response: DaemonApiResponse): void {
	const idx = getMessageIndex(response);
	if (idx === -1) {
		return;
	}
	messages.value[idx].response.push(JSON.stringify(response, null, 4));
	if (response.data.rsp.lastWave && response.data.rsp.progress === 100) {
		componentState.value = ComponentState.Ready;
	}
}

function handleBackupResponse(response: DaemonApiResponse): void {
	const idx = getMessageIndex(response);
	if (idx === -1) {
		return;
	}
	messages.value[idx].response.push(JSON.stringify(response, null, 4));
	if (response.data.rsp.progress === 100) {
		componentState.value = ComponentState.Ready;
	}
}

function handleMessageError(response: DaemonApiResponse): void {
	const idx = messages.value.findIndex((item: JsonMessage) => item.msgId === response.data.msgId);
	if (idx === -1) {
		return;
	}
	messages.value[idx].response.push(JSON.stringify(response, null, 4));
	componentState.value = ComponentState.Ready;
}

function handleResponse(response: DaemonApiResponse): void {
	const idx = getMessageIndex(response);
	if (idx === -1) {
		return;
	}
	messages.value[idx].response.push(JSON.stringify(response, null, 4));
	componentState.value = ComponentState.Ready;
}

function getMessageIndex(response: DaemonApiResponse): number {
	return messages.value.findIndex((item: JsonMessage) => item.msgId === response.data.msgId && item.mType === response.mType);
}

function clearMessages(): void {
	messages.value = [];
}
</script>
