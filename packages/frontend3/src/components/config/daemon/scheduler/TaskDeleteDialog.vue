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
	<DeleteModalWindow
		ref='dialog'
		:component-state='componentState'
		:tooltip='$t("components.config.daemon.scheduler.actions.delete")'
		@submit='removeTask()'
	>
		<template #title>
			{{ $t('components.config.daemon.scheduler.delete.title') }}
		</template>
		{{ $t('components.config.daemon.scheduler.delete.prompt', { id: componentProps.taskId }) }}
	</DeleteModalWindow>
</template>

<script lang='ts' setup>
import { SchedulerMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SchedulerService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { type ApiResponseManagementRsp, type TApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { type SchedulerRemoveTaskResult } from '@iqrf/iqrf-gateway-daemon-utils/types/management';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import DeleteModalWindow from '@/components/DeleteModalWindow.vue';
import { useDaemonStore } from '@/store/daemonSocket';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const componentProps = defineProps({
	taskId: {
		type: String,
		required: true,
	},
});
const emit = defineEmits(['deleted']);
const dialog: Ref<typeof DeleteModalWindow | null> = ref(null);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const msgId: Ref<string | null> = ref(null);

daemonStore.$onAction(
	({ name, after }) => {
		if (name !== 'onMessage') {
			return;
		}
		after((rsp: TApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			if (rsp.mType === SchedulerMessages.RemoveTask) {
				handleRemoveTask(rsp as ApiResponseManagementRsp<SchedulerRemoveTaskResult>);
			}
		});
	},
);

async function removeTask(): Promise<void> {
	componentState.value = ComponentState.Saving;
	const opts = new DaemonMessageOptions(
		30_000,
		null,
		() => {
			componentState.value = ComponentState.Ready;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		SchedulerService.removeTask(
			{},
			{ clientId: SchedulerService.ClientID, taskId: componentProps.taskId },
			opts,
		),
	);
}

function handleRemoveTask(rsp: ApiResponseManagementRsp<SchedulerRemoveTaskResult>): void {
	if (rsp.data.status !== 0) {
		toast.error('TODO ERROR HANDLING');
		componentState.value = ComponentState.Error;
		return;
	}
	toast.success(
		i18n.t('components.config.daemon.scheduler.messages.delete.success', { id: componentProps.taskId }),
	);
	componentState.value = ComponentState.Ready;
	dialog.value?.close();
	emit('deleted');
}
</script>
