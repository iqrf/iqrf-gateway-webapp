<template>
	<v-form
		ref='form'
		:disabled='componentState === ComponentState.Action'
	>
		<ICard flat tile>
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.frc-response-time.title') }}
			</v-card-title>
			<ISelectInput
				v-model='command'
				:label='$t("components.iqrfnet.network-manager.frc-response-time.command")'
				:items='commandOptions'
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Download'
					:loading='componentState === ComponentState.Action'
					:text='$t("components.iqrfnet.network-manager.frc-response-time.actions.get")'
					@click='frcResponseTime()'
				/>
				<FrcResponseTimeResultDialog
					ref='resultModal'
					:result='result'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { FrcCommand } from '@iqrf/iqrf-gateway-daemon-utils/enums/embed';
import { MaintenanceService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { IqmeshFrcResponseTimeParams } from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, ISelectInput } from '@iqrf/iqrf-vue-ui';
import {
	onBeforeUnmount,
	ref,
	Ref,
	type TemplateRef,
	useTemplateRef,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import FrcResponseTimeResultDialog from '@/components/iqrfnet/network-manager/FrcResponseTimeResultDialog.vue';
import { DaemonApiSendError } from '@/errors/DaemonApiSendError';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';
import { FrcResponseTimeResult } from '@/types/DaemonApi/Iqmesh';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const command: Ref<FrcCommand> = ref(FrcCommand.Iqrf1Byte);
const commandOptions = [
	{
		title: i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.iqrf2bits'),
		value: FrcCommand.Iqrf2Bits,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.iqrf1byte'),
		value: FrcCommand.Iqrf1Byte,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.iqrf2byte'),
		value: FrcCommand.Iqrf2Bytes,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.iqrf4byte'),
		value: FrcCommand.Iqrf4Bytes,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.user2bits'),
		value: FrcCommand.User2Bits,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.user1byte'),
		value: FrcCommand.User1Byte,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.user2byte'),
		value: FrcCommand.User2Bytes,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.user4byte'),
		value: FrcCommand.User4Bytes,
	},
];
const form: TemplateRef<VForm> = useTemplateRef('form');
const resultModal: TemplateRef<InstanceType<typeof FrcResponseTimeResultDialog>> = useTemplateRef('resultModal');
const result: Ref<FrcResponseTimeResult | null> = ref(null);
const msgId: Ref<string | null> = ref(null);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === IqmeshServiceMessages.MaintenanceFrcResponseTime) {
				handleFrcResponseTime(rsp);
			}
		});
	}
});

async function frcResponseTime(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	result.value = null;
	const opts = new DaemonMessageOptions(
		null,
		400_000,
		i18n.t('components.iqrfnet.network-manager.frc-response-time.messages.get.timeout'),
		() => {
			componentState.value = ComponentState.Idle;
			msgId.value = null;
		},
	);
	const params: IqmeshFrcResponseTimeParams = {
		command: command.value,
	};
	try {
		msgId.value = await daemonStore.sendMessage(
			MaintenanceService.frcResponseTime(
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

function handleFrcResponseTime(rsp: DaemonApiResponse): void {
	switch (rsp.data.status) {
		case 0:
			rsp.data.rsp.command = command.value;
			result.value = rsp.data.rsp as FrcResponseTimeResult;
			resultModal.value?.open();
			break;
		case 1_003:
			toast.info(i18n.t('common.messages.noNodes'));
			break;
		case 1_004:
			toast.error(i18n.t(
				'components.iqrfnet.network-manager.frc-response-time.messages.get.noResponded',
			));
			break;
		case 1_005:
			toast.error(i18n.t(
				'components.iqrfnet.network-manager.frc-response-time.messages.get.noHandled',
			));
			break;
		default:
			toast.error(i18n.t(
				'components.iqrfnet.network-manager.frc-response-time.messages.get.failed',
			));
	}
}

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
