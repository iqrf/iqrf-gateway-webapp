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
	<DeleteModalWindow
		ref='dialog'
		:component-state='componentState'
		:tooltip='$t("components.config.daemon.scheduler.actions.delete")'
		@submit='removeTask'
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
import { type DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
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
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			if (rsp.mType === SchedulerMessages.RemoveTask.toString()) {
				handleRemoveTask(rsp);
			}
		});
	},
);

function removeTask(): void {
	componentState.value = ComponentState.Saving;
	const options = new DaemonMessageOptions(
		null,
		30_000,
		null,
		() => {msgId.value = null;},
	);
	daemonStore.sendMessage(
		SchedulerService.removeTask(componentProps.taskId, options),
	).then((val: string) => msgId.value = val);
}

function handleRemoveTask(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error('TODO ERROR HANDLING');
		componentState.value = ComponentState.Error;
	}
	toast.success(
		i18n.t('components.config.daemon.scheduler.messages.delete.success', { id: componentProps.taskId }),
	);
	componentState.value = ComponentState.Ready;
	dialog.value?.close();
	emit('deleted');
}
</script>
