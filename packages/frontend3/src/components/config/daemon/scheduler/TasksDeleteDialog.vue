<template>
	<v-dialog
		v-model='show'
		scrollable
		persistent
		no-click-animation
		:width='width'
	>
		<template #activator='{ props }'>
			<v-btn
				v-bind='props'
				id='tasks-delete-activator'
				color='white'
				size='large'
				:icon='mdiDelete'
			/>
			<v-tooltip
				activator='#tasks-delete-activator'
				location='bottom'
			>
				{{ $t('components.configuration.daemon.scheduler.actions.deleteAll') }}
			</v-tooltip>
		</template>
		<Card>
			<template #title>
				{{ $t('components.configuration.daemon.scheduler.deleteAll.title') }}
			</template>
			{{ $t('components.configuration.daemon.scheduler.deleteAll.prompt') }}
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='componentState === ComponentState.Saving'
					@click='removeTasks'
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
	</v-dialog>
</template>

<script lang='ts' setup>
import { type DaemonApiResponse, SchedulerMessages, SchedulerService, DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils';
import { mdiDelete } from '@mdi/js';
import {
	ref,
	type Ref,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import { getModalWidth } from '@/helpers/modal';
import { useDaemonStore } from '@/store/daemonSocket';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const emit = defineEmits(['deleted']);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const width = getModalWidth();
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
			if (rsp.mType === SchedulerMessages.RemoveAll) {
				handleRemoveTasks(rsp);
			}
		});
	},
);

function removeTasks(): void {
	componentState.value = ComponentState.Saving;
	const options = new DaemonMessageOptions(
		null,
		30000,
		null,
		() => {msgId.value = null;},
	);
	daemonStore.sendMessage(
		SchedulerService.removeAllTasks(options),
	).then((val: string) => msgId.value = val);
}

function handleRemoveTasks(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error('TODO ERROR HANDLING');
		componentState.value = ComponentState.Error;
	}
	toast.success(
		i18n.t('components.configuration.daemon.scheduler.messages.deleteAll.success'),
	);
	componentState.value = ComponentState.Ready;
	close();
	emit('deleted');
}

function close(): void {
	show.value = false;
}
</script>
