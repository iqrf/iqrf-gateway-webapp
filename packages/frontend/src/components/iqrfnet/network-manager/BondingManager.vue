<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='componentState === ComponentState.Action'
	>
		<ICard flat tile>
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.bonding.title') }}
			</v-card-title>
			<ISelectInput
				v-model='bondingMethod'
				:label='$t("components.iqrfnet.network-manager.bonding.method")'
				:items='bondingMethodOptions'
			/>
			<v-switch
				v-model='autoAddress'
				:label='$t("components.iqrfnet.common.autoAddress")'
				color='primary'
				inset
			/>
			<INumberInput
				v-if='!autoAddress'
				v-model='address'
				:label='$t("components.iqrfnet.common.address")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.common.validation.address.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.common.validation.address.integer")),
					(v: number) => ValidationRules.between(v, 1, 239, $t("components.iqrfnet.common.validation.address.between")),
				]'
				:min='1'
				:max='239'
				required
			/>
			<ITextInput
				v-if='bondingMethod === BondingMethod.SMART_CONNECT'
				v-model='scCode'
				:label='$t("components.iqrfnet.network-manager.bonding.smartConnectCode")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.bonding.validation.smartConnectCode.required")),
					(v: string) => ValidationRules.regex(v, /^[1-9a-km-tv-zA-HJ-NP-Z]{34}$/, $t("components.iqrfnet.network-manager.bonding.validation.smartConnectCode.regex")),
				]'
				required
			/>
			<INumberInput
				v-model='retries'
				:label='$t("components.iqrfnet.common.bondingTestRetries")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.common.validation.bondingTestRetries.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.common.validation.bondingTestRetries.integer")),
					(v: number) => ValidationRules.between(v, 0, 3, $t("components.iqrfnet.common.validation.bondingTestRetries.between")),
				]'
				:min='0'
				:max='3'
				required
			/>
			<v-checkbox
				v-model='unbondC'
				:label='$t("components.iqrfnet.network-manager.bonding.unbondCoordinator")'
				hide-details
			/>
			<template #actions>
				<IActionBtn
					v-if='bondingMethod === BondingMethod.LOCAL'
					:action='Action.Custom'
					color='primary'
					:loading='componentState === ComponentState.Action'
					:text='$t("components.iqrfnet.network-manager.bonding.actions.bond")'
					:disabled='!isValid.value'
					@click='bond()'
				/>
				<IActionBtn
					v-else
					:action='Action.Custom'
					color='primary'
					:loading='componentState === ComponentState.Action'
					:text='$t("components.iqrfnet.network-manager.bonding.actions.smartConnect")'
					:disabled='!isValid.value'
					@click='smartConnect()'
				/>
				<UnbondNodeDialog
					:address='address'
					:coordinator-only='unbondC'
					@update-devices='emit("updateDevices")'
				/>
				<ClearBondsDialog
					:coordinator-only='unbondC'
					@update-devices='emit("updateDevices")'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { BondingService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { IqmeshBondNodeParams, IqmeshSmartConnectParams } from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, INumberInput, ISelectInput, ITextInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { onBeforeUnmount, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import ClearBondsDialog from '@/components/iqrfnet/network-manager//ClearBondsDialog.vue';
import UnbondNodeDialog from '@/components/iqrfnet/network-manager/UnbondNodeDialog.vue';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';

enum BondingMethod {
	LOCAL = 0,
	SMART_CONNECT = 1,
}

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const emit = defineEmits<{
  updateDevices: []
}>();
const i18n = useI18n();
const daemonStore = useDaemonStore();
const msgId: Ref<string | null> = ref(null);
const form: Ref<VForm | null> = ref(null);
const bondingMethod: Ref<BondingMethod> = ref(BondingMethod.LOCAL);
const bondingMethodOptions = [
	{
		title: i18n.t('components.iqrfnet.network-manager.bonding.methods.local'),
		value: BondingMethod.LOCAL,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.bonding.methods.smartConnect'),
		value: BondingMethod.SMART_CONNECT,
	},
];
const autoAddress: Ref<boolean> = ref(false);
const address: Ref<number> = ref(1);
const scCode: Ref<string> = ref('');
const retries: Ref<number> = ref(1);
const unbondC: Ref<boolean> = ref(false);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === IqmeshServiceMessages.BondNode) {
				handleBond(rsp);
			} else if (rsp.mType === IqmeshServiceMessages.SmartConnect) {
				handleSmartConnect(rsp);
			}
		});
	}
});


async function bond(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params: IqmeshBondNodeParams = {
		deviceAddr: autoAddress.value ? 0 : address.value,
		bondingTestRetries: retries.value,
	};
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t(''),
		() => {
			componentState.value = ComponentState.Idle;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		BondingService.bondNode(
			{ repeat: 1, returnVerbose: true },
			params,
			opts,
		),
	);
}

function handleBond(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		const addr = autoAddress.value ? rsp.data.rsp.assignedAddr : address.value;
		toast.success(
			i18n.t('components.iqrfnet.network-manager.bonding.messages.success', { address: addr }),
		);
		emit('updateDevices');
		return;
	}
	handleBondError(rsp);
}

function handleBondError(rsp: DaemonApiResponse): void {
	let message = '';
	switch (rsp.data.status) {
		case 1_003:
			message = i18n.t('components.iqrfnet.network-manager.bonding.messages.addressTaken', { address: address.value });
			break;
		case 1_004:
			message = i18n.t('components.iqrfnet.network-manager.bonding.messages.noAddressAvailable');
			break;
		default:
			message = i18n.t('components.iqrfnet.network-manager.bonding.messages.error');
	}
	toast.error(message);
}

async function smartConnect(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params: IqmeshSmartConnectParams = {
		deviceAddr: autoAddress.value ? 0 : address.value,
		smartConnectCode: scCode.value,
		bondingTestRetries: retries.value,
	};
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t(''),
		() => {
			componentState.value = ComponentState.Idle;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		BondingService.smartConnect(
			{ repeat: 1, returnVerbose: true },
			params,
			opts,
		),
	);
}

function handleSmartConnect(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		const addr = autoAddress.value ? rsp.data.rsp.assignedAddr : address.value;
		toast.success(
			i18n.t('components.iqrfnet.network-manager.bonding.messages.success', { address: addr }),
		);
		emit('updateDevices');
		return;
	}
	handleBondError(rsp);
}

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
