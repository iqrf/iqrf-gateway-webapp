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
				:action='Action.Reset'
				color='red'
				:icon='mdiDelete'
				container-type='card-title'
				:disabled='disabled'
			/>
		</template>
		<ICard :action='Action.Delete'>
			<template #title>
				{{ $t('components.iqrfnet.standard-manager.standard-devices.reset.title') }}
			</template>
			{{ $t('components.iqrfnet.standard-manager.standard-devices.reset.text') }}
			<template #actions>
				<IActionBtn
					:action='Action.Reset'
					:icon='mdiDelete'
					color='red'
					:loading='componentState === ComponentState.Action'
					:disabled='disabled'
					@click='reset()'
				/>
				<v-spacer />
				<IActionBtn
					:action='Action.Close'
					:disabled='disabled'
					@click='close()'
				/>
			</template>
		</ICard>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { DbMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { DbService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IModalWindow,
} from '@iqrf/iqrf-vue-ui';
import { mdiDelete } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { DaemonApiSendError } from '@/errors/DaemonApiSendError';
import { useDaemonStore } from '@/store/daemonSocket';

const componentProps = withDefaults(
	defineProps<{
		disabled?: boolean;
	}>(),
	{
		disabled: false,
	},
);
const emit = defineEmits<{
	reset: [];
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const daemonStore = useDaemonStore();
const i18n = useI18n();
const msgId: Ref<string | null> = ref(null);
const show: Ref<boolean> = ref(false);
daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === DbMessages.Reset) {
				handleReset(rsp);
			}
		});
	}
});

async function reset(): Promise<void> {
	const opts = new DaemonMessageOptions(
		null,
		10_000,
		i18n.t('components.iqrfnet.standard-manager.standard-devices.messages.reset.timeout'),
		() => {
			componentState.value = ComponentState.Idle;
			msgId.value = null;
		},
	);
	try {
		msgId.value = await daemonStore.sendMessage(
			DbService.resetDatabase(opts),
		);
	} catch (error) {
		if (error instanceof DaemonApiSendError) {
			console.error(error);
			toast.error(error.message);
		}
		componentState.value = ComponentState.Idle;
	}
}

function handleReset(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.iqrfnet.standard-manager.standard-devices.messages.reset.failed', { error: rsp.data.rsp.errorStr }),
		);
		return;
	}
	toast.success(
		i18n.t('components.iqrfnet.standard-manager.standard-devices.messages.reset.success'),
	);
	emit('reset');
	close();

}

function close(): void {
	show.value = false;
}
</script>
