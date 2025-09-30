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
			<IActionBtn
				v-bind='props'
				:action='Action.Custom'
				color='red'
				:text='$t("components.iqrfnet.network-manager.bonding.actions.unbond")'
			/>
		</template>
		<ICard header-color='red'>
			<template #title>
				{{ $t('components.iqrfnet.network-manager.bonding.unbond.title') }}
			</template>
			{{ $t('components.iqrfnet.network-manager.bonding.unbond.text', { address: address }) }}
			<template #actions>
				<IActionBtn
					:action='Action.Custom'
					color='red'
					:loading='componentState === ComponentState.Action'
					:text='$t("components.iqrfnet.network-manager.bonding.actions.unbond")'
					@click='unbond()'
				/>
				<v-spacer />
				<IActionBtn
					:action='Action.Cancel'
					:disabled='componentState === ComponentState.Action'
					@click='close()'
				/>
			</template>
		</ICard>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { BondingService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, IModalWindow } from '@iqrf/iqrf-vue-ui';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useDaemonStore } from '@/store/daemonSocket';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const emit = defineEmits<{
  updateDevices: []
}>();
const componentProps = defineProps({
	address: {
		type: Number,
		required: true,
	},
	coordinatorOnly: {
		type: Boolean,
		required: true,
	},
});
const daemonStore = useDaemonStore();
const msgId: Ref<string | null> = ref(null);
const i18n = useI18n();
const show: Ref<boolean> = ref(false);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === IqmeshServiceMessages.RemoveBond || rsp.mType === IqmeshServiceMessages.RemoveBondCoordinator) {
				handleUnbond(rsp);
			}
		});
	}
});

async function unbond(): Promise<void> {
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.iqrfnet.network-manager.bonding.messages.unbond.timeout'),
		() => {
			componentState.value = ComponentState.Idle;
			msgId.value = null;
		},
	);
	if (componentProps.coordinatorOnly) {
		msgId.value = await daemonStore.sendMessage(
			BondingService.removeBondCoordinator(
				{ repeat: 1, returnVerbose: true },
				{ deviceAddr: [componentProps.address] },
				opts,
			),
		);
	} else {
		msgId.value = await daemonStore.sendMessage(
			BondingService.removeBond(
				{ repeat: 1, returnVerbose: true },
				{ deviceAddr: componentProps.address },
				opts,
			),
		);
	}
}

function handleUnbond(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		toast.success(
			i18n.t(
				componentProps.coordinatorOnly ?
					'components.iqrfnet.network-manager.bonding.messages.unbond.successCoordinator' :
					'components.iqrfnet.network-manager.bonding.messages.unbond.success',
				{ address: componentProps.address },
			),
		);
		emit('updateDevices');
		close();
		return;
	}
	let message = '';
	switch (rsp.data.status) {
		case -1:
			message = i18n.t('common.messages.offlineDevice', { address: componentProps.address });
			break;
		case 1:
			if (componentProps.coordinatorOnly) {
				message = i18n.t('common.messages.noDevice', { address: componentProps.address });
				break;
			}
		// eslint-disable-next-line no-fallthrough
		case 8:
			message = i18n.t('common.messages.noDevice', { address: componentProps.address });
			break;
		default:
			message = i18n.t(
				componentProps.coordinatorOnly ?
					'components.iqrfnet.network-manager.bonding.messages.unbond.failedCoordinator' :
					'components.iqrfnet.network-manager.bonding.messages.unbond.failed',
				{ address: componentProps.address },
			);
	}
	toast.error(message);
}

function close(): void {
	show.value = false;
}

</script>
