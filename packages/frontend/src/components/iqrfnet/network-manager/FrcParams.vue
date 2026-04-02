<template>
	<v-form
		:disabled='componentState === ComponentState.Action'
	>
		<ICard flat>
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.dpa-params.frc-params.title') }}
			</v-card-title>
			<ISelectInput
				v-model='responseTime'
				:label='$t("components.iqrfnet.network-manager.dpa-params.frc-params.responseTime")'
				:items='responseTimeOptions'
				:description='$t("components.iqrfnet.network-manager.dpa-params.frc-params.notes.responseTime")'
			/>
			<v-checkbox
				v-model='offlineFrc'
				:label='$t("components.iqrfnet.network-manager.dpa-params.frc-params.offlineFrc")'
				:hint='$t("components.iqrfnet.network-manager.dpa-params.frc-params.notes.offlineFrc")'
				density='compact'
				persistent-hint
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Download'
					:loading='componentState === ComponentState.Action && actionType === IqmeshConfigAction.Get'
					:disabled='componentState === ComponentState.Action && actionType !== IqmeshConfigAction.Get'
					:text='$t("components.iqrfnet.network-manager.dpa-params.frc-params.actions.get")'
					@click='frcParamsAction(IqmeshConfigAction.Get)'
				/>
				<IActionBtn
					:action='Action.Upload'
					:loading='componentState === ComponentState.Action && actionType === IqmeshConfigAction.Set'
					:disabled='componentState === ComponentState.Action && actionType !== IqmeshConfigAction.Set'
					:text='$t("components.iqrfnet.network-manager.dpa-params.frc-params.actions.set")'
					@click='frcParamsAction(IqmeshConfigAction.Set)'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { FrcResponseTime } from '@iqrf/iqrf-gateway-daemon-utils/enums/embed';
import { IqmeshConfigAction } from '@iqrf/iqrf-gateway-daemon-utils/enums/iqmesh';
import { DpaParametersService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { IqmeshFrcParams } from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, ISelectInput } from '@iqrf/iqrf-vue-ui';
import { onBeforeUnmount, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { DaemonApiSendError } from '@/errors/DaemonApiSendError';
import { useDaemonStore } from '@/store/daemonSocket';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const responseTime: Ref<FrcResponseTime> = ref(FrcResponseTime.MS40);
const responseTimeOptions = [
	{
		title: i18n.t('components.iqrfnet.network-manager.dpa-params.frc-params.responseTimes.0'),
		value: FrcResponseTime.MS40,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.dpa-params.frc-params.responseTimes.16'),
		value: FrcResponseTime.MS360,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.dpa-params.frc-params.responseTimes.32'),
		value: FrcResponseTime.MS680,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.dpa-params.frc-params.responseTimes.48'),
		value: FrcResponseTime.MS1320,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.dpa-params.frc-params.responseTimes.64'),
		value: FrcResponseTime.MS2600,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.dpa-params.frc-params.responseTimes.80'),
		value: FrcResponseTime.MS5160,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.dpa-params.frc-params.responseTimes.96'),
		value: FrcResponseTime.MS10280,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.dpa-params.frc-params.responseTimes.112'),
		value: FrcResponseTime.MS20520,
	},
];
const offlineFrc: Ref<boolean> = ref(false);
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
			if (rsp.mType === IqmeshServiceMessages.FrcParams) {
				handleFrcParamsAction(rsp);
			}
		});
	}
});

async function frcParamsAction(action: IqmeshConfigAction): Promise<void> {
	actionType.value = action;
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		5_000,
		i18n.t(
			actionType.value === IqmeshConfigAction.Get ?
				'components.iqrfnet.network-manager.dpa-params.frc-params.messages.get.timeout' :
				'components.iqrfnet.network-manager.dpa-params.frc-params.messages.set.timeout',
		),
		() => {
			componentState.value = ComponentState.Idle;
			msgId.value = null;
		},
	);
	const params: IqmeshFrcParams = {
		action: actionType.value,
	};
	if (actionType.value === IqmeshConfigAction.Set) {
		params.responseTime = responseTime.value;
		params.offlineFrc = offlineFrc.value;
	}
	try {
		msgId.value = await daemonStore.sendMessage(
			DpaParametersService.frcParams(
				{ repeat: 1, returnVerbose: true },
				params,
				opts,
			),
		);
	} catch (error) {
		if (error instanceof DaemonApiSendError) {
			console.error(error);
			toast.error(error.message);
		}
		componentState.value = ComponentState.Idle;
	}
}

function handleFrcParamsAction(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		toast.success(
			i18n.t(
				actionType.value === IqmeshConfigAction.Get ?
					'components.iqrfnet.network-manager.dpa-params.frc-params.messages.get.success' :
					'components.iqrfnet.network-manager.dpa-params.frc-params.messages.set.success',
			),
		);
		if (actionType.value === IqmeshConfigAction.Get) {
			responseTime.value = rsp.data.rsp.responseTime;
			offlineFrc.value = rsp.data.rsp.offlineFrc;
		}
	} else {
		toast.error(
			i18n.t(
				actionType.value === IqmeshConfigAction.Get ?
					'components.iqrfnet.network-manager.dpa-params.frc-params.messages.get.failed' :
					'components.iqrfnet.network-manager.dpa-params.frc-params.messages.set.failed',
			),
		);
	}
	componentState.value = ComponentState.Idle;
}

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
