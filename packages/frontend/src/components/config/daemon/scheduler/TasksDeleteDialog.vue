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
	<IDeleteModalWindow
		ref='dialog'
		:component-state='componentState'
		persistent
		@submit='removeTasks()'
	>
		<template #activator='{ props }'>
			<IActionBtn
				v-bind='props'
				:action='Action.Delete'
				container-type='card-title'
				:loading='componentState === ComponentState.Action'
				:disabled='disabled'
				:tooltip='$t("components.config.daemon.scheduler.actions.deleteAll")'
			/>
		</template>
		<template #title>
			{{ $t('components.config.daemon.scheduler.deleteAll.title') }}
		</template>
		{{ $t('components.config.daemon.scheduler.deleteAll.prompt') }}
	</IDeleteModalWindow>
</template>

<script lang='ts' setup>
import { SchedulerMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SchedulerService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { type DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import {
	Action,
	ComponentState,
	IActionBtn,
	IDeleteModalWindow,
} from '@iqrf/iqrf-vue-ui';
import { ref, type Ref, type TemplateRef, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useDaemonStore } from '@/store/daemonSocket';

defineProps({
	disabled: {
		type: Boolean,
		required: false,
		default: false,
	},
});
const emit = defineEmits<{
	deleted: [];
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const dialog: TemplateRef<InstanceType<typeof IDeleteModalWindow>> = useTemplateRef('dialog');
const i18n = useI18n();
const daemonStore = useDaemonStore();
const msgId: Ref<string | null> = ref(null);

daemonStore.$onAction(
	({ name, after }) => {
		if (name !== 'onMessage') {
			return;
		}
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			if (rsp.mType === SchedulerMessages.RemoveAll) {
				handleRemoveTasks(rsp);
			}
		});
	},
);

async function removeTasks(): Promise<void> {
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.config.daemon.scheduler.messages.deleteAll.timeout'),
		() => {
			componentState.value = ComponentState.Idle;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		SchedulerService.removeAllTasks(opts),
	);
}

function handleRemoveTasks(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.config.daemon.scheduler.messages.deleteAll.failed'),
		);
		componentState.value = ComponentState.Idle;
		return;
	}
	toast.success(
		i18n.t('components.config.daemon.scheduler.messages.deleteAll.success'),
	);
	componentState.value = ComponentState.Idle;
	dialog.value?.close();
	emit('deleted');
}
</script>
