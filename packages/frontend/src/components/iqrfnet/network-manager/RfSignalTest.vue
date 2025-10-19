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
				v-model='target'
				:label='$t("components.iqrfnet.network-manager.rf-signal.target")'
				:items='targetOptions'
			/>
			<INumberInput
				v-model='rfChannel'
				:label='$t("components.iqrfnet.common.rfChannel")'
				:rules='rfChannelRules'
				:min='0'
				:max='rfChannelMax'
				required
			/>
			<INumberInput
				v-model='rxFilter'
				:label='$t("components.iqrfnet.common.rxFilter")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.common.validation.rxFilter.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.common.validation.rxFilter.integer")),
					(v: number) => ValidationRules.between(v, 0, 64, $t("components.iqrfnet.common.validation.rxFilter.between")),
				]'
				:min='0'
				:max='64'
				required
			/>
			<RfMeasurementTimeInput
				v-model='measurementTime'
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Download'
					:loading='componentState === ComponentState.Action'
					:text='$t("components.iqrfnet.network-manager.rf-signal.actions.test")'
					@click='testRf()'
				/>
				<RfSignalTestResultDialog
					ref='resultModal'
					:result='result'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { IqmeshTestRfMeasurementTime } from '@iqrf/iqrf-gateway-daemon-utils/enums/iqmesh';
import { MaintenanceService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { IqmeshTestRfSignalParams } from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, INumberInput, ISelectInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { computed, onBeforeUnmount, ref, Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import RfMeasurementTimeInput
	from '@/components/iqrfnet/network-manager/RfMeasurementTimeInput.vue';
import RfSignalTestResultDialog
	from '@/components/iqrfnet/network-manager/RfSignalTestResultDialog.vue';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';
import { RfSignalTestResult } from '@/types/DaemonApi/Iqmesh';

enum Targets {
	Coordinator = 0,
	Nodes = 1,
}

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
defineExpose({
	setRfChannel,
});
const i18n = useI18n();
const daemonStore = useDaemonStore();
const target: Ref<Targets> = ref(Targets.Coordinator);
const targetOptions = [
	{
		title: i18n.t('components.iqrfnet.network-manager.rf-signal.targets.coordinator'),
		value: Targets.Coordinator,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.rf-signal.targets.nodes'),
		value: Targets.Nodes,
	},
];
const rfBand: Ref<number> = ref(868);
const rfChannel: Ref<number> = ref(52);
const rxFilter: Ref<number> = ref(0);
const measurementTime: Ref<IqmeshTestRfMeasurementTime> = ref(IqmeshTestRfMeasurementTime.MS5160);
const form: Ref<VForm|null> = useTemplateRef('form');
const resultModal: Ref<InstanceType<typeof RfSignalTestResultDialog>|null> = useTemplateRef('resultModal');
const result: Ref<RfSignalTestResult[]> = ref([]);
const msgId: Ref<string | null> = ref(null);

const rfChannelMax = computed(() => {
	if (rfBand.value === 433) {
		return 16;
	} else if (rfBand.value === 868) {
		return 67;
	} else {
		return 255;
	}
});

const rfChannelRules = computed(() => {
	const rules = [
		(v: number|null) => ValidationRules.required(v, i18n.t('components.iqrfnet.common.validation.rfChannel.required')),
		(v: number) => ValidationRules.integer(v, i18n.t('components.iqrfnet.common.validation.rfChannel.integer')),
	];
	if (rfBand.value === 433) {
		rules.push(
			(v: number) => ValidationRules.between(v, 0, 16, i18n.t('components.iqrfnet.common.validation.rfChannel.between433')),
		);
	} else if (rfBand.value === 868) {
		rules.push(
			(v: number) => ValidationRules.between(v, 0, 67, i18n.t('components.iqrfnet.common.validation.rfChannel.between868')),
		);
	} else {
		rules.push(
			(v: number) => ValidationRules.between(v, 0, 255, i18n.t('components.iqrfnet.common.validation.rfChannel.between916')),
		);
	}
	return rules;
});

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === IqmeshServiceMessages.MaintenanceTestRf) {
				handleTestRf(rsp);
			}
		});
	}
});

async function testRf(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	result.value = [];
	const opts = new DaemonMessageOptions(
		null,
		330_000,
		i18n.t('components.iqrfnet.network-manager.rf-signal.messages.timeout'),
		() => {
			componentState.value = ComponentState.Idle;
			msgId.value = null;
		},
	);
	const params: IqmeshTestRfSignalParams = {
		deviceAddr: target.value === Targets.Coordinator ? 0 : 255,
		measurementTime: measurementTime.value,
		rfChannel: rfChannel.value,
		rxFilter: rxFilter.value,
	};
	msgId.value = await daemonStore.sendMessage(
		MaintenanceService.testRf(
			{ repeat: 1, returnVerbose: true },
			params,
			opts,
		),
	);
}

function handleTestRf(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		result.value = rsp.data.rsp.testRfResult as RfSignalTestResult[];
		resultModal.value?.open();
	} else if (rsp.data.status === 1_003) {
		toast.info(
			i18n.t('common.messages.noNodes'),
		);
	} else {
		toast.error(
			i18n.t('components.iqrfnet.network-manager.rf-signal.messages.failed'),
		);
	}
}

function setRfChannel(band: number): void {
	rfBand.value = band;
	if (rfBand.value === 433) {
		rfChannel.value = 16;
	} else if (rfBand.value === 868) {
		rfChannel.value = 52;
	} else {
		rfChannel.value = 255;
	}
}

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
