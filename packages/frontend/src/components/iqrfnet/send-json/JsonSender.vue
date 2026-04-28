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
				rel='noopener noreferrer'
				:prepend-icon='mdiBook'
			>
				{{ $t('common.labels.apiDocs') }}
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
			<CodeEditor
				v-model='json'
				:label='$t("components.iqrfnet.send-json.json")'
				language='json'
				clearable
				required
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.iqrfnet.send-json.validation.request.required")),
					(v: string) => ValidationRules.json(v, $t("components.iqrfnet.send-json.validation.request.json")),
					(v: string) => validate(v),
				]'
			/>
			<v-btn
				class='mt-2'
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
import { ComponentState, ICard, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiBook, mdiSend } from '@mdi/js';
import { ref, type Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';
import { z } from 'zod';

import CodeEditor from '@/components/iqrfnet/send-json/CodeEditor.vue';
import RequestHistory from '@/components/iqrfnet/send-json/RequestHistory.vue';
import { DaemonApiSendError } from '@/errors/DaemonApiSendError';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';
import { type JsonApiTransaction } from '@/types/Iqrfnet';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const daemonStore = useDaemonStore();
const form: Ref<VForm|null> = useTemplateRef('form');
const msgId: Ref<string | null> = ref(null);
const json: Ref<string | undefined> = ref(undefined);
const messages: Ref<JsonApiTransaction[]> = ref([]);
const i18n = useI18n();

daemonStore.$onAction(
	({ name, after }) => {
		if (name === 'onMessage') {
			after((rsp: DaemonApiResponse) => {
				if (rsp.data.msgId !== msgId.value) {
					return;
				}
				switch (rsp.mType) {
					case IqmeshServiceMessages.Autonetwork:
						handleAutonetworkResponse(rsp);
						break;
					case IqmeshServiceMessages.Backup:
						handleBackupResponse(rsp);
						break;
					case DbMessages.Enumerate:
						handleEnumerateResponse(rsp);
						break;
					case GenericMessages.MessageError:
						handleMessageError(rsp);
						break;
					default:
						daemonStore.removeMessage(msgId.value);
						handleResponse(rsp);
				}
			});
		}
	},
);

const schema = z.object({
	mType: z.string({
		error: (issue) => {
			if (issue.input === undefined) {
				return i18n.t('components.iqrfnet.send-json.validation.properties.mType.required');
			}
			return i18n.t('components.iqrfnet.send-json.validation.properties.mType.type');
		},
	}),
	data: z.object({
		msgId: z.string({
			error: i18n.t('components.iqrfnet.send-json.validation.properties.msgId.type'),
		})
			.optional(),
		req: z.object({
			deviceAddr: z.number({
				error: i18n.t('components.iqrfnet.send-json.validation.properties.deviceAddr.type'),
			}).int({
				error: i18n.t('components.iqrfnet.send-json.validation.properties.deviceAddr.type'),
			}).refine((v: number) => (v >= 0 && v <= 239) || v === 255, {
				error: i18n.t('components.iqrfnet.send-json.validation.properties.deviceAddr.value'),
			})
				.optional(),
			nAdr: z.number({
				error: i18n.t('components.iqrfnet.send-json.validation.properties.nAdr.type'),
			}).int({
				error: i18n.t('components.iqrfnet.send-json.validation.properties.nAdr.type'),
			}).refine((v: number) => (v >= 0 && v <= 239) || (v >= 252 && v <= 255), {
				error: i18n.t('components.iqrfnet.send-json.validation.properties.nAdr.value'),
			})
				.optional(),
			hwpId: z.number({
				error: i18n.t('components.iqrfnet.send-json.validation.properties.hwpId.type'),
			}).int({
				error: i18n.t('components.iqrfnet.send-json.validation.properties.hwpId.type'),
			}).refine((v: number) => v >= 0 && v <= 65_535, {
				error: i18n.t('components.iqrfnet.send-json.validation.properties.hwpId.value'),
			})
				.optional(),
		}).loose()
			.optional(),
		returnVerbose: z.boolean({
			error: i18n.t('components.iqrfnet.send-json.validation.properties.returnVerbose.type'),
		})
			.optional(),
		repeat: z.number({
			error: i18n.t('components.iqrfnet.send-json.validation.properties.repeat.type'),
		}).int({
			error: i18n.t('components.iqrfnet.send-json.validation.properties.repeat.type'),
		}).min(1, {
			error: i18n.t('components.iqrfnet.send-json.validation.properties.repeat.value'),
		})
			.optional(),
		timeout: z.number({
			error: i18n.t('components.iqrfnet.send-json.validation.properties.timeout.type'),
		}).int({
			error: i18n.t('components.iqrfnet.send-json.validation.properties.timeout.type'),
		}).min(500, {
			error: i18n.t('components.iqrfnet.send-json.validation.properties.timeout.value'),
		})
			.optional(),
	}, {
		error: (issue) => {
			if (issue.input === undefined) {
				return i18n.t('components.iqrfnet.send-json.validation.properties.data.required');
			}
			return i18n.t('components.iqrfnet.send-json.validation.properties.data.type');
		},
	}),
});

function validate(value: string): true|string {
	const request = JSON.parse(value) as DaemonApiRequest;
	const result = schema.safeParse(request);
	if (!result.success) {
		return result.error.issues.map((item: z.core.$ZodIssue) => {
			return `${item.message} (${item.path.join('.')})`;
		}).join('\n');
	}
	return true;
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || !json.value) {
		return;
	}
	const request = JSON.parse(json.value!) as DaemonApiRequest;
	componentState.value = ComponentState.Action;
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
	try {
		msgId.value = await daemonStore.sendMessage(options);
		messages.value.unshift({
			msgId: msgId.value,
			mType: request.mType,
			timestamp: new Date().toLocaleString(),
			request: JSON.stringify(request, null, 4),
			response: [],
		});
	} catch (error) {
		if (error instanceof DaemonApiSendError) {
			console.error(error);
			toast.error(error.message);
		}
		componentState.value = ComponentState.Idle;
	}
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
