<template>
	<IModalWindow
		v-model='modelValue'
		persistent
	>
		<ICard header-color='warning'>
			<template #title>
				{{ $t('components.iqrfnet.tr-config.disable.title') }}
			</template>
			{{ $t('components.iqrfnet.tr-config.disable.text') }}
			<template #actions>
				<IActionBtn
					:action='Action.Custom'
					:icon='mdiUploadOff'
					color='warning'
					:text='$t("components.iqrfnet.tr-config.disable.action")'
					:loading='componentState === ComponentState.Action'
					@click='disableHandler()'
				/>
				<v-spacer />
				<IActionBtn
					:action='Action.Close'
					:disabled='componentState === ComponentState.Action'
					@click='close()'
				/>
			</template>
		</ICard>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { EmbedOsMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { OsService } from '@iqrf/iqrf-gateway-daemon-utils/services/embed';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, IModalWindow } from '@iqrf/iqrf-vue-ui';
import { mdiUploadOff } from '@mdi/js';
import { onBeforeUnmount, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useDaemonStore } from '@/store/daemonSocket';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const emit = defineEmits<{
	restart: [],
}>();
const modelValue = defineModel({
	type: Boolean,
	required: true,
});
const componentProps = defineProps({
	deviceAddr: {
		type: Number,
		required: true,
	},
});
const i18n = useI18n();
const daemonStore = useDaemonStore();
const msgId: Ref<string | null> = ref(null);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			switch (rsp.mType) {
				case EmbedOsMessages.WriteTrConfigByte:
					handleDisableHandler(rsp);
					break;
			}
		});
	}
});

async function disableHandler(): Promise<void> {
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.iqrfnet.tr-config.messages.disable.timeout'),
		() => {
			componentState.value = ComponentState.Ready;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		OsService.writeTrConfigByte(
			{ addr: componentProps.deviceAddr, returnVerbose: true },
			{
				bytes: [
					{
						address: 5,
						value: 128,
						mask: 255,
					},
				],
			},
			opts,
		),
	);
}

function handleDisableHandler(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		componentState.value = ComponentState.Idle;
		toast.error(
			i18n.t('components.iqrfnet.tr-config.messages.disable.failed'),
		);
		return;
	}
	emit('restart');
	close();
}

function close(): void {
	modelValue.value = false;
}

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});
</script>
