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
			<ISelectInput
				v-model='mode'
				:items='modeOptions'
				:disabled='mode === DaemonMode.Unknown'
				hide-details
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Save'
					container-type='card'
					:disabled='mode === DaemonMode.Unknown'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { DaemonMode, ManagementMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { ManagementService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, ISelectInput } from '@iqrf/iqrf-vue-ui';
import { onBeforeMount, onBeforeUnmount, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useDaemonStore } from '@/store/daemonSocket';

enum ModeActions {
	GET = 0,
	SET = 1,
}

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const msgId: Ref<string | null> = ref(null);
const modeAction: Ref<ModeActions | null> = ref(null);
const mode: Ref<DaemonMode> = ref(DaemonMode.Unknown);
const modeOptions = ref([
	{
		title: i18n.t('components.gateway.mode.modes.operational'),
		value: DaemonMode.Operational,
	},
	{
		title: i18n.t('components.gateway.mode.modes.forwarding'),
		value: DaemonMode.Forwarding,
	},
	{
		title: i18n.t('components.gateway.mode.modes.service'),
		value: DaemonMode.Service,
	},
]);

daemonStore.$onAction(
	({ name, after }) => {
		if (name === 'onMessage') {
			after((rsp: DaemonApiResponse) => {
				if (rsp.data.msgId !== msgId.value) {
					return;
				}
				daemonStore.removeMessage(msgId.value);
				if (rsp.mType === ManagementMessages.Mode) {
					handleModeResponse(rsp);
				}
			});
		}
	},
);

async function getMode(): Promise<void> {
	componentState.value = ComponentState.Loading;
	modeAction.value = ModeActions.GET;
	const opts = new DaemonMessageOptions(
		null,
		5_000,
		'components.gateway.mode.current.messages.get.timeout',
		() => {
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(ManagementService.getMode(opts));
}

async function setMode(): Promise<void> {
	componentState.value = ComponentState.Action;
	modeAction.value = ModeActions.SET;
	const opts = new DaemonMessageOptions(
		null,
		5_000,
		'components.gateway.mode.current.messages.set.timeout',
		() => {
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(ManagementService.setMode(mode.value, opts));
}

function handleModeResponse(rsp: Record<string, any>): void {
	if (rsp.data.status !== 0) {
		if (modeAction.value === ModeActions.GET) {
			toast.error(
				i18n.t('components.gateway.mode.current.messages.get.failed'),
			);
		} else {
			toast.error(
				i18n.t('components.gateway.mode.current.messages.set.failed'),
			);
			componentState.value = ComponentState.Idle;
		}
		return;
	}
	if (modeAction.value === ModeActions.GET) {
		mode.value = rsp.data.rsp.operMode;
	} else if (modeAction.value === ModeActions.SET) {
		toast.success(
			i18n.t(
				'components.gateway.mode.current.messages.set.success',
				{ mode: i18n.t(`components.gateway.mode.modes.${mode.value}`) },
			),
		);
	}
}

onBeforeMount(() => {
	getMode();
});

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});
</script>
