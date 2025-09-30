<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='componentState === ComponentState.Action'
		@submit.prevent='discovery()'
	>
		<ICard flat>
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.discovery.title') }}
			</v-card-title>
			<v-alert
				class='mb-4'
				variant='tonal'
				type='info'
				:text='$t("components.iqrfnet.network-manager.discovery.note")'
			/>
			<INumberInput
				v-model='discoveryParams.maxAddr'
				:label='$t("components.iqrfnet.network-manager.discovery.maxAddr")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.discovery.validation.maxAddr.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.network-manager.discovery.validation.maxAddr.integer")),
					(v: number) => ValidationRules.between(v, 0, 239, $t("components.iqrfnet.network-manager.discovery.validation.maxAddr.between")),
				]'
				:min='0'
				:max='239'
				required
				:prepend-inner-icon='mdiNumeric'
			/>
			<INumberInput
				v-model='discoveryParams.txPower'
				:label='$t("components.iqrfnet.network-manager.discovery.txPower")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.discovery.validation.txPower.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.network-manager.discovery.validation.txPower.integer")),
					(v: number) => ValidationRules.between(v, 0, 7, $t("components.iqrfnet.network-manager.discovery.validation.txPower.between")),
				]'
				:min='0'
				:max='7'
				required
				:prepend-inner-icon='mdiSignalVariant'
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Custom'
					color='primary'
					:icon='mdiPlay'
					:loading='componentState === ComponentState.Action'
					:disabled='!isValid.value'
					:text='$t("components.iqrfnet.network-manager.discovery.actions.run")'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { EmbedCoordinatorMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { CoordinatorService } from '@iqrf/iqrf-gateway-daemon-utils/services/embed';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DiscoveryParams } from '@iqrf/iqrf-gateway-daemon-utils/types/embed';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, INumberInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiNumeric, mdiPlay, mdiSignalVariant } from '@mdi/js';
import { ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const emit = defineEmits<{
  updateDevices: []
}>();
const i18n = useI18n();
const daemonStore = useDaemonStore();
const form: Ref<VForm | null> = ref(null);
const discoveryParams: Ref<DiscoveryParams> = ref({
	maxAddr: 239,
	txPower: 6,
});
const msgId: Ref<string | null> = ref(null);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === EmbedCoordinatorMessages.Discovery) {
				handleDiscovery(rsp);
			}
		});
	}
});

async function discovery(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(null);
	const params = { ...discoveryParams.value };
	msgId.value = await daemonStore.sendMessage(
		CoordinatorService.discovery(
			{ addr: 0 },
			params,
			opts,
		),
	);
}

function handleDiscovery(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		toast.success(
			i18n.t('components.iqrfnet.network-manager.discovery.messages.success', { count: rsp.data.rsp.result.discNr }),
		);
		emit('updateDevices');
	} else if (rsp.data.status === -1) {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.discovery.messages.timeout'),
		);
	} else {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.discovery.messages.failed'),
		);
	}
	componentState.value = ComponentState.Idle;
}

</script>
