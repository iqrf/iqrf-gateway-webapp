<template>
	<v-form
		:disabled='componentState === ComponentState.Action'
	>
		<ICard flat tile>
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.dpa-params.dpa-value.title') }}
			</v-card-title>
			<v-alert
				class='mb-4'
				type='info'
				variant='tonal'
				:text='$t("components.iqrfnet.network-manager.dpa-params.dpa-value.notes.value")'
			/>
			<ISelectInput
				v-model='valueType'
				:label='$t("components.iqrfnet.network-manager.dpa-params.dpa-value.type")'
				:items='valueOptions'
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Download'
					:loading='componentState === ComponentState.Action && actionType === IqmeshConfigAction.Get'
					:disabled='componentState === ComponentState.Action && actionType !== IqmeshConfigAction.Get'
					:text='$t("components.iqrfnet.network-manager.dpa-params.dpa-value.actions.get")'
					@click='dpaValueAction(IqmeshConfigAction.Get)'
				/>
				<IActionBtn
					:action='Action.Upload'
					:loading='componentState === ComponentState.Action && actionType === IqmeshConfigAction.Set'
					:disabled='componentState === ComponentState.Action && actionType !== IqmeshConfigAction.Set'
					:text='$t("components.iqrfnet.network-manager.dpa-params.dpa-value.actions.set")'
					@click='dpaValueAction(IqmeshConfigAction.Set)'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { DpaParam } from '@iqrf/iqrf-gateway-daemon-utils/enums/embed';
import { IqmeshConfigAction } from '@iqrf/iqrf-gateway-daemon-utils/enums/iqmesh';
import { DpaParametersService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { IqmeshDpaValueParams } from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, ISelectInput } from '@iqrf/iqrf-vue-ui';
import { onBeforeUnmount, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useDaemonStore } from '@/store/daemonSocket';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const valueType: Ref<DpaParam> = ref(DpaParam.Rssi);
const valueOptions = [
	{
		title: i18n.t('components.iqrfnet.network-manager.dpa-params.dpa-value.types.rssi'),
		value: DpaParam.Rssi,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.dpa-params.dpa-value.types.voltage'),
		value: DpaParam.Voltage,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.dpa-params.dpa-value.types.system'),
		value: DpaParam.System,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.dpa-params.dpa-value.types.user'),
		value: DpaParam.UserSpecified,
	},
];
const actionType: Ref<IqmeshConfigAction> = ref(IqmeshConfigAction.Get);
const msgId: Ref<string | null> = ref(null);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === IqmeshServiceMessages.DpaValue) {
				handleDpaValueAction(rsp);
			}
		});
	}
});

async function dpaValueAction(action: IqmeshConfigAction): Promise<void> {
	actionType.value = action;
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		5_000,
		i18n.t(
			actionType.value === IqmeshConfigAction.Get ?
				'components.iqrfnet.network-manager.dpa-params.dpa-value.messages.get.timeout' :
				'components.iqrfnet.network-manager.dpa-params.dpa-value.messages.set.timeout',
		),
		() => {
			componentState.value = ComponentState.Idle;
		},
	);
	const params: IqmeshDpaValueParams = {
		action: actionType.value,
	};
	if (actionType.value === IqmeshConfigAction.Set) {
		params.type = valueType.value;
	}
	msgId.value = await daemonStore.sendMessage(
		DpaParametersService.dpaValue(
			{ repeat: 1, returnVerbose: true },
			params,
			opts,
		),
	);
}

function handleDpaValueAction(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		toast.success(
			i18n.t(
				actionType.value === IqmeshConfigAction.Get ?
					'components.iqrfnet.network-manager.dpa-params.dpa-value.messages.get.success' :
					'components.iqrfnet.network-manager.dpa-params.dpa-value.messages.set.success',
			),
		);
		if (actionType.value === IqmeshConfigAction.Get) {
			valueType.value = rsp.data.rsp.type;
		}
	} else {
		toast.error(
			i18n.t(
				actionType.value === IqmeshConfigAction.Get ?
					'components.iqrfnet.network-manager.dpa-params.dpa-value.messages.get.failed' :
					'components.iqrfnet.network-manager.dpa-params.dpa-value.messages.set.failed',
			),
		);
	}
	componentState.value = ComponentState.Idle;
}

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
