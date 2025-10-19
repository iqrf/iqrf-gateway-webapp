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
	<ICard>
		<template #title>
			{{ $t('pages.iqrfnet.send-json.title') }}
		</template>
		<template #titleActions>
			<v-btn
				size='small'
				href='https://docs.iqrf.org/iqrf-gateway/user/daemon/api.html'
				target='_blank'
			>
				{{ $t('common.labels.apidoc') }}
			</v-btn>
		</template>
		<v-alert
			v-if='componentState === ComponentState.Action'
			variant='tonal'
			color='info'
			:text='$t("components.iqrfnet.inProgress")'
			class='mb-2'
		/>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Action'
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
				:disabled='!isValid.value || componentState === ComponentState.Action'
				@click='onSubmit()'
			>
				<v-icon :icon='mdiSend' />
				{{ $t('common.buttons.send') }}
			</v-btn>
		</v-form>
	</ICard>
	<RequestHistory
		class='mt-4'
		:messages='messages'
		@clear='clearMessages()'
	/>
</template>

<script lang='ts' setup>
import {
	DbMessages,
	EmbedOsMessages,
	GenericMessages,
	IqmeshServiceMessages,
} from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { DaemonApiRequest, DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { ComponentState, ICard } from '@iqrf/iqrf-vue-ui';
import { mdiSend } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { VForm } from 'vuetify/components';

import RequestHistory from '@/components/iqrfnet/send-json/RequestHistory.vue';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';
import { type JsonApiTransaction } from '@/types/Iqrfnet';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const daemonStore = useDaemonStore();
const form: Ref<VForm | null> = ref(null);
const msgId: Ref<string | null> = ref(null);
const json: Ref<string | null> = ref(null);
const messages: Ref<JsonApiTransaction[]> = ref([]);

daemonStore.$onAction(
	({ name, after }) => {
		if (name === 'onMessage') {
			after((rsp: DaemonApiResponse) => {
				if (rsp.data.msgId !== msgId.value) {
					return;
				}
				if (rsp.mType === IqmeshServiceMessages.Autonetwork) {
					handleAutonetworkResponse(rsp);
				} else if (rsp.mType === IqmeshServiceMessages.Backup) {
					handleBackupResponse(rsp);
				} else if (rsp.mType === DbMessages.Enumerate) {
					handleEnumerateResponse(rsp);
				} else if (rsp.mType === GenericMessages.MessageError) {
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
	componentState.value = ComponentState.Action;
	const request = JSON.parse(json.value) as DaemonApiRequest;
	const options = new DaemonMessageOptions(null);
	if (request.data.req && request.data.req.nAdr === 255) { // if a message is broadcasted, do not wait for proper response
		options.timeout = 1_000;
	} else if (request.mType === EmbedOsMessages.Batch || request.mType === EmbedOsMessages.SelectiveBatch) { // batch and selective batch requests do not have proper responses, do not wait
		options.timeout = 1_000;
	} else if (request.mType === IqmeshServiceMessages.Autonetwork ||
		request.mType === IqmeshServiceMessages.Backup ||
		(request.mType === DbMessages.Enumerate) ||
		request.mType === IqmeshServiceMessages.OtaUpload) { // requests without timeout
	} else { // regular messages have a minute timeout
		options.timeout = 60_000;
		options.message = 'iqrfnet.sendJson.messages.error.fail';
	}
	options.callback = () => {
		componentState.value = ComponentState.Idle;
		msgId.value = null;
	};
	options.request = request;
	msgId.value = await daemonStore.sendMessage(options);
	messages.value.unshift({
		msgId: msgId.value,
		mType: request.mType,
		timestamp: new Date().toLocaleString(),
		request: JSON.stringify(request, null, 4),
		response: [],
	});
}

function handleAutonetworkResponse(rsp: DaemonApiResponse): void {
	const idx = getMessageIndex(rsp);
	if (idx === -1) {
		return;
	}
	messages.value[idx].response.push(JSON.stringify(rsp, null, 4));
	if (rsp.data.rsp.lastWave && rsp.data.rsp.progress === 100) {
		daemonStore.removeMessage(msgId.value);
		componentState.value = ComponentState.Ready;
	}
}

function handleBackupResponse(rsp: DaemonApiResponse): void {
	const idx = getMessageIndex(rsp);
	if (idx === -1) {
		return;
	}
	messages.value[idx].response.push(JSON.stringify(rsp, null, 4));
	if (rsp.data.rsp.progress === 100) {
		daemonStore.removeMessage(msgId.value);
		componentState.value = ComponentState.Ready;
	}
}

function handleEnumerateResponse(rsp: DaemonApiResponse): void {
	const idx = getMessageIndex(rsp);
	if (idx === -1) {
		return;
	}
	messages.value[idx].response.push(JSON.stringify(rsp, null, 4));
	if (rsp.data.rsp.step === 8) {
		daemonStore.removeMessage(msgId.value);
		componentState.value = ComponentState.Ready;
	}
}

function handleMessageError(rsp: DaemonApiResponse): void {
	const idx = messages.value.findIndex((item: JsonApiTransaction) => item.msgId === rsp.data.msgId);
	if (idx === -1) {
		return;
	}
	messages.value[idx].response.push(JSON.stringify(rsp, null, 4));
	componentState.value = ComponentState.Ready;
}

function handleResponse(rsp: DaemonApiResponse): void {
	const idx = getMessageIndex(rsp);
	if (idx === -1) {
		return;
	}
	messages.value[idx].response.push(JSON.stringify(rsp, null, 4));
	componentState.value = ComponentState.Ready;
}

function getMessageIndex(rsp: DaemonApiResponse): number {
	return messages.value.findIndex((item: JsonApiTransaction) => item.msgId === rsp.data.msgId && item.mType === rsp.mType);
}

function clearMessages(): void {
	messages.value = [];
}
</script>
