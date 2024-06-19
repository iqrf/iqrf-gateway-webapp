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
				<v-icon :icon='mdiSend' />
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
import { type DaemonApiResponse, type DaemonApiRequest, type JsonMessage } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { mdiSend } from '@mdi/js';
import { type Ref, ref } from 'vue';
import { VForm } from 'vuetify/components';

import RequestHistory from '@/components/iqrfnet/send-json/RequestHistory.vue';
import Card from '@/components/layout/card/Card.vue';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';
import { ComponentState } from '@/types/ComponentState';


const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const daemonStore = useDaemonStore();
const form: Ref<VForm | null> = ref(null);
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
	const request: DaemonApiRequest = JSON.parse(json.value) as DaemonApiRequest;
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
