<template>
	<v-form
		ref='form'
		:disabled='componentState === ComponentState.Action'
		@submit.prevent='bondNfc()'
	>
		<ICard flat tile>
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.bondNfc.title') }}
			</v-card-title>
			<v-alert
				class='mb-4'
				variant='tonal'
				type='info'
				:text='$t("components.iqrfnet.network-manager.bondNfc.note")'
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Custom'
					color='primary'
					:icon='mdiSignalVariant'
					:loading='componentState === ComponentState.Action'
					:text='$t("components.iqrfnet.network-manager.bondNfc.title")'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { GenericMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { CoordinatorService } from '@iqrf/iqrf-gateway-daemon-utils/services/embed';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard } from '@iqrf/iqrf-vue-ui';
import { mdiSignalVariant } from '@mdi/js';
import { onBeforeUnmount, ref, Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const form: Ref<VForm|null> = useTemplateRef('form');
const msgId: Ref<string | null> = ref(null);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === GenericMessages.Raw) {
				handleBondNfc(rsp);
			}
		});
	}
});

async function bondNfc(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		12_000,
		i18n.t('components.iqrfnet.network-manager.bondNfc.messages.timeout'),
		() => {
			componentState.value = ComponentState.Idle;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		CoordinatorService.bondIquip(opts),
	);
}

function handleBondNfc(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		toast.success(
			i18n.t('components.iqrfnet.network-manager.bondNfc.messages.success'),
		);
	} else if (rsp.data.status === -1) {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.bondNfc.messages.timeout'),
		);
	} else {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.bondNfc.messages.failed'),
		);
	}
	componentState.value = ComponentState.Idle;
}

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
