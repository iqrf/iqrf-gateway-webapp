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
	<v-form @submit.prevent='setMode()'>
		<ICard>
			<template #title>
				{{ $t('components.gateway.mode.current.title') }}
			</template>
			<v-select
				v-model='mode.operMode'
				:items='modeOptions'
				:disabled='mode.operMode === DaemonMode.Unknown'
			/>
			<template #actions>
				<ICardActionBtn
					:action='Action.Save'
					:disabled='mode.operMode === DaemonMode.Unknown'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { DaemonMode, ManagementMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { ManagementService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import {
	type ApiResponseManagement,
	type ApiResponseManagementRsp,
	type TApiResponse,
} from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonGetModeResult, DaemonSetModeParams } from '@iqrf/iqrf-gateway-daemon-utils/types/management';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ICard, ICardActionBtn } from '@iqrf/iqrf-vue-ui';
import { computed, ComputedRef, onBeforeMount, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useDaemonStore } from '@/store/daemonSocket';
import { SelectItem } from '@/types/vuetify';

const i18n = useI18n();
const daemonStore = useDaemonStore();
const msgId: Ref<string | null> = ref(null);

const mode: Ref<DaemonSetModeParams> = ref({
	operMode: DaemonMode.Unknown,
});
const modeOptions: ComputedRef<SelectItem[]> = computed(() => {
	const modes = Object.values(DaemonMode);
	return modes.map((item: DaemonMode): SelectItem => {
		return {
			title: i18n.t(`components.gateway.mode.modes.${item}`),
			value: item,
		};
	});
});

daemonStore.$onAction(
	({ name, after }) => {
		if (name === 'onMessage') {
			after((rsp: TApiResponse) => {
				if (rsp.data.msgId !== msgId.value) {
					return;
				}
				daemonStore.removeMessage(msgId.value);
				if (rsp.mType === ManagementMessages.GetMode) {
					handleGetMode(rsp as ApiResponseManagementRsp<DaemonGetModeResult>);
				} else if (rsp.mType === ManagementMessages.SetMode) {
					handleSetMode(rsp);
				}
			});
		}
	},
);

async function getMode(): Promise<void> {
	const opts = new DaemonMessageOptions(
		5_000,
		'components.gateway.mode.current.messages.get.timeout',
		() => {
			msgId.value = null;
		},
	);
	const rq = ManagementService.getMode({}, opts);
	msgId.value = await daemonStore.sendMessage(rq);
}

function handleGetMode(response: ApiResponseManagementRsp<DaemonGetModeResult>): void {
	if (response.data.status !== 0) {
		toast.error(
			i18n.t('components.gateway.mode.current.messages.get.failed'),
		);
		return;
	}
	mode.value.operMode = response.data.rsp.operMode;
}

async function setMode(): Promise<void> {
	const opts = new DaemonMessageOptions(
		5_000,
		'components.gateway.mode.messages.setTimeout',
		() => {msgId.value = null;},
	);
	msgId.value = await daemonStore.sendMessage(ManagementService.setMode({}, mode.value, opts));
}

function handleSetMode(response: ApiResponseManagement): void {
	if (response.data.status !== 0) {
		toast.error(
			i18n.t('components.gateway.mode.current.messages.set.failed'),
		);
		return;
	}
	toast.success(
		i18n.t(
			'components.gateway.mode.current.messages.set.success',
			{ mode: i18n.t(`components.gateway.mode.modes.${mode.value.operMode}`) },
		),
	);
}

onBeforeMount(() => {
	getMode();
});
</script>
