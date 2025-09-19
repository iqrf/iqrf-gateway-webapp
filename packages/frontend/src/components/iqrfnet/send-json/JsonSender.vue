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
				{{ $t('components.iqrfnet.send-json.api') }}
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
				@click='onSubmit'
			>
				<v-icon :icon='mdiSend' />
				{{ $t('common.buttons.send') }}
			</v-btn>
		</v-form>
	</ICard>
	<RequestHistory
		class='mt-4'
		:messages='messages'
		@clear='clearMessages'
	/>
</template>

<script lang='ts' setup>
import {
	EmbedCoordinatorMessages,
	EmbedOsMessages,
	GenericMessages,
	IqmeshServiceMessages,
} from '@iqrf/iqrf-gateway-daemon-utils/enums';
import {
	type ApiRequestEmbedReq,
	type ApiResponseIqmesh,
	type ApiResponseMessageError,
	type IqmeshAutonetworkResult,
	type IqmeshBackupResult,
	type TApiRequest,
	type TApiResponse,
	type TMessageErrorResponse,
} from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { ComponentState, ICard } from '@iqrf/iqrf-vue-ui';
import { mdiSend } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { VForm } from 'vuetify/components';

import RequestHistory from '@/components/iqrfnet/send-json/RequestHistory.vue';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';
import { type JsonApiTransaction } from '@/types/Iqrfnet';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const daemonStore = useDaemonStore();
const form: Ref<VForm | null> = ref(null);
const msgId: Ref<string | null> = ref(null);
const json: Ref<string | null> = ref(null);
const messages: Ref<JsonApiTransaction[]> = ref([]);

daemonStore.$onAction(
	({ name, after }) => {
		if (name === 'onMessage') {
			after((rsp: TApiResponse) => {
				if (rsp.data.msgId !== msgId.value) {
					return;
				}
				if (rsp.mType === IqmeshServiceMessages.Autonetwork) {
					handleAutonetworkResponse(rsp as ApiResponseIqmesh<IqmeshAutonetworkResult>);
				} else if (rsp.mType === IqmeshServiceMessages.Backup) {
					handleBackupResponse(rsp as ApiResponseIqmesh<IqmeshBackupResult>);
				} else if (rsp.mType === GenericMessages.MessageError) {
					handleMessageError(rsp as ApiResponseMessageError<TMessageErrorResponse>);
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
	const request: TApiRequest = JSON.parse(json.value) as TApiRequest;
	const options = DaemonMessageOptions.withRequest<TApiRequest>(request, null, null, () => {
		msgId.value = null;
		componentState.value = ComponentState.Ready;
	});
	if (checkIfNoResponseMessage(options as DaemonMessageOptions<ApiRequestEmbedReq>)) {
		options.timeout = 1_000;
	} else if (request.mType === EmbedCoordinatorMessages.Discovery ||
		request.mType === IqmeshServiceMessages.Autonetwork ||
		request.mType === IqmeshServiceMessages.Backup ||
		request.mType === IqmeshServiceMessages.OtaUpload
	) {
		options.timeout = null;
		options.message = null;
	} else {
		options.timeout = 60_000;
	}
	msgId.value = await daemonStore.sendMessage(options);
	messages.value.unshift({
		msgId: msgId.value,
		mType: request.mType,
		timestamp: new Date().toLocaleString(),
		request: JSON.stringify(request, null, 4),
		response: [],
	});
}

function checkIfNoResponseMessage(options: DaemonMessageOptions<ApiRequestEmbedReq>): boolean {
	return (options.request !== null && options.request.data.req !== undefined &&
		{}.hasOwnProperty.call(options.request.data.req, 'nAdr') && options.request.data.req.nAdr === 255) ||
		options.request?.mType === EmbedOsMessages.Batch || options.request?.mType === EmbedOsMessages.SelectiveBatch;
}

function handleAutonetworkResponse(rsp: ApiResponseIqmesh<IqmeshAutonetworkResult>): void {
	const idx = getMessageIndex(rsp);
	if (idx === -1) {
		return;
	}
	messages.value[idx].response.push(JSON.stringify(rsp, null, 4));
	if (rsp.data.rsp.lastWave && rsp.data.rsp.progress === 100) {
		componentState.value = ComponentState.Ready;
	}
}

function handleBackupResponse(response: ApiResponseIqmesh<IqmeshBackupResult>): void {
	const idx = getMessageIndex(response);
	if (idx === -1) {
		return;
	}
	messages.value[idx].response.push(JSON.stringify(response, null, 4));
	if (response.data.rsp.progress === 100) {
		componentState.value = ComponentState.Ready;
	}
}

function handleMessageError(response: ApiResponseMessageError<TMessageErrorResponse>): void {
	const idx = messages.value.findIndex((item: JsonApiTransaction) => item.msgId === response.data.msgId);
	if (idx === -1) {
		return;
	}
	messages.value[idx].response.push(JSON.stringify(response, null, 4));
	componentState.value = ComponentState.Ready;
}

function handleResponse(response: TApiResponse): void {
	const idx = getMessageIndex(response);
	if (idx === -1) {
		return;
	}
	messages.value[idx].response.push(JSON.stringify(response, null, 4));
	componentState.value = ComponentState.Ready;
}

function getMessageIndex(response: TApiResponse): number {
	return messages.value.findIndex((item: JsonApiTransaction) => item.msgId === response.data.msgId && item.mType === response.mType);
}

function clearMessages(): void {
	messages.value = [];
}
</script>
