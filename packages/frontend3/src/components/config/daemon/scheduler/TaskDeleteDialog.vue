<template>
	<ModalWindow v-model='show'>
		<template #activator='{ props }'>
			<v-icon
				v-bind='props'
				color='error'
				size='large'
				:icon='mdiDelete'
			/>
		</template>
		<Card>
			<template #title>
				{{ $t('components.configuration.daemon.scheduler.delete.title') }}
			</template>
			{{ $t('components.configuration.daemon.scheduler.delete.prompt', {id: componentProps.taskId}) }}
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='componentState === ComponentState.Saving'
					@click='removeTask'
				>
					{{ $t('common.buttons.delete') }}
				</v-btn>
				<v-spacer />
				<v-btn
					color='grey-darken-2'
					variant='elevated'
					:disabled='componentState === ComponentState.Saving'
					@click='close'
				>
					{{ $t('common.buttons.close') }}
				</v-btn>
			</template>
		</Card>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { SchedulerMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SchedulerService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { type DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { mdiDelete } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import ModalWindow from '@/components/ModalWindow.vue';
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
const i18n = useI18n();
const daemonStore = useDaemonStore();
const show: Ref<boolean> = ref(false);
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
			if (rsp.mType === SchedulerMessages.RemoveTask) {
				handleRemoveTask(rsp);
			}
		});
	},
);

function removeTask(): void {
	componentState.value = ComponentState.Saving;
	const options = new DaemonMessageOptions(
		null,
		30000,
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
		i18n.t('components.configuration.daemon.scheduler.messages.delete.success', {id: componentProps.taskId}),
	);
	componentState.value = ComponentState.Ready;
	close();
	emit('deleted');
}

function close(): void {
	show.value = false;
}
</script>
