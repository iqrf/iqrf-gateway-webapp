<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='componentState === ComponentState.Action'
	>
		<ICard flat>
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.autonetwork.form.params.title') }}
			</v-card-title>
			<INumberInput
				v-model='params.actionRetries'
				:label='$t("components.iqrfnet.common.actionRetries")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.common.validation.actionRetries.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.common.validation.actionRetries.integer")),
					(v: number) => ValidationRules.between(v, 0, 3, $t("components.iqrfnet.common.validation.actionRetries.between")),
				]'
				:min='0'
				:max='3'
				required
			/>
			<INumberInput
				v-model='params.discoveryTxPower'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.params.discoveryTxPower")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.common.validation.txPower.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.common.validation.txPower.integer")),
					(v: number) => ValidationRules.between(v, 0, 7, $t("components.iqrfnet.common.validation.txPower.between")),
				]'
				:min='0'
				:max='7'
				required
			/>
			<v-checkbox
				v-model='params.discoveryBeforeStart'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.params.discoveryBeforeStart")'
				density='compact'
				hide-details
			/>
			<v-checkbox
				v-model='params.skipDiscoveryEachWave'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.params.skipDiscoveryEachWave")'
				density='compact'
				hide-details
			/>
			<v-checkbox
				v-model='params.unbondUnrespondingNodes'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.params.unbondUnrespondingNodes")'
				density='compact'
				hide-details
			/>
			<v-checkbox
				v-model='params.skipPrebonding'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.params.skipPrebonding")'
				density='compact'
				hide-details
			/>
			<v-divider class='my-2' />
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.autonetwork.form.stopConditions.title') }}
			</v-card-title>
			<INumberInput
				v-model='params.stopConditions.waves'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.stopConditions.waves")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.autonetwork.validation.waves.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.network-manager.autonetwork.validation.waves.integer")),
					(v: number) => ValidationRules.between(v, 1, 127, $t("components.iqrfnet.network-manager.autonetwork.validation.waves.between")),
				]'
				:min='1'
				:max='127'
				required
			/>
			<INumberInput
				v-model='params.stopConditions.emptyWaves'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.stopConditions.emptyWaves")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.autonetwork.validation.emptyWaves.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.network-manager.autonetwork.validation.emptyWaves.integer")),
					(v: number) => ValidationRules.between(v, 1, 127, $t("components.iqrfnet.network-manager.autonetwork.validation.emptyWaves.between")),
				]'
				:min='1'
				:max='127'
				required
			/>
			<v-checkbox
				v-model='useNodes'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.stopConditions.useNodes")'
				density='compact'
				hide-details
			/>
			<div v-if='useNodes'>
				<ISelectInput
					v-model='nodeCondition'
					:label='$t("components.iqrfnet.network-manager.autonetwork.form.stopConditions.nodeCondition")'
					:items='nodeConditionOptions'
				/>
				<INumberInput
					v-if='nodeCondition === NodeCondition.Total'
					v-model='params.stopConditions.numberOfTotalNodes'
					:label='$t("components.iqrfnet.network-manager.autonetwork.form.stopConditions.nodeConditions.total")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.autonetwork.validation.nodes.required")),
						(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.network-manager.autonetwork.validation.nodes.integer")),
						(v: number) => ValidationRules.between(v, 1, 239, $t("components.iqrfnet.network-manager.autonetwork.validation.nodes.between")),
					]'
					:min='1'
					:max='239'
					required
				/>
				<INumberInput
					v-else
					v-model='params.stopConditions.numberOfNewNodes'
					:label='$t("components.iqrfnet.network-manager.autonetwork.form.stopConditions.nodeConditions.new")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.autonetwork.validation.nodes.required")),
						(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.network-manager.autonetwork.validation.nodes.integer")),
						(v: number) => ValidationRules.between(v, 1, 239, $t("components.iqrfnet.network-manager.autonetwork.validation.nodes.between")),
					]'
					:min='1'
					:max='239'
					required
				/>
			</div>
			<v-checkbox
				v-model='params.stopConditions.abortOnTooManyNodesFound'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.stopConditions.abortOnTooManyNodesFound")'
				density='compact'
				hide-details
			/>
			<v-divider class='my-2' />
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.autonetwork.form.bondingControl.title') }}
			</v-card-title>
			<v-checkbox
				v-model='useAddressSpace'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.bondingControl.addressSpace.use")'
				density='compact'
				:hint='$t("components.iqrfnet.network-manager.autonetwork.notes.addressSpace")'
				persistent-hint
			/>
			<ITextInput
				v-if='useAddressSpace'
				v-model='addressSpace'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.bondingControl.addressSpace.title")'
				class='mt-2'
				:description='$t("components.iqrfnet.network-manager.autonetwork.notes.addressSpaceFormat", { range: "1,2,4,<10;20>" })'
			>
				<template #append-inner>
					{{ params.addressSpace.length }}
				</template>
			</ITextInput>
			<v-checkbox
				v-model='useMidList'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.bondingControl.mid.use")'
				density='compact'
				:hint='$t("components.iqrfnet.network-manager.autonetwork.notes.midControl")'
				persistent-hint
			/>
			<div v-if='useMidList'>
				<IDataTable
					:headers='midHeaders'
					:items='params.midList!'
					:hover='true'
					:dense='true'
					disable-column-filtering
					disable-search
					no-data-text='components.iqrfnet.network-manager.autonetwork.midlist.noDataText'
				>
					<template #top>
						<v-toolbar
							class='rounded-t mt-2'
							color='primary'
							density='compact'
						>
							<v-toolbar-title>
								{{ $t("components.iqrfnet.network-manager.autonetwork.midlist.title") }}
							</v-toolbar-title>
							<v-toolbar-items>
								<MidForm
									:action='Action.Add'
									:existing-records='params.midList ?? []'
									@add-record='(r: AutonetworkMidList) => params.midList?.push(r)'
								/>
								<IActionBtn
									class='rounded-te'
									:action='Action.Delete'
									container-type='card-title'
									:tooltip='$t("components.iqrfnet.network-manager.autonetwork.midlist.actions.deleteAll")'
									@click='params.midList = []'
								/>
							</v-toolbar-items>
						</v-toolbar>
					</template>
					<template #item.actions='{ item, index }'>
						<MidForm
							:action='Action.Edit'
							:mid-record='toRaw(item)'
							:index='index'
							:existing-records='params.midList ?? []'
							@edit-record='(idx: number, r: AutonetworkMidList) => params.midList![idx] = r'
						/>
						<IDataTableAction
							:action='Action.Delete'
							:tooltip='$t("components.iqrfnet.network-manager.autonetwork.midlist.actions.delete")'
							@click='() => params.midList?.splice(index, 1)'
						/>
					</template>
				</IDataTable>
				<v-checkbox
					v-model='params.midFiltering'
					:label='$t("components.iqrfnet.network-manager.autonetwork.form.bondingControl.mid.filter")'
					density='compact'
					:hint='$t("components.iqrfnet.network-manager.autonetwork.notes.midFilter")'
					persistent-hint
				/>
			</div>
			<v-checkbox
				v-model='useOverlappingNetworks'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.bondingControl.overlappingNetworks.use")'
				density='compact'
				hide-details
			/>
			<div v-if='useOverlappingNetworks'>
				<INumberInput
					v-model='params.overlappingNetworks!.networks'
					:label='$t("components.iqrfnet.network-manager.autonetwork.form.bondingControl.overlappingNetworks.networks")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.autonetwork.validation.networks.required")),
						(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.network-manager.autonetwork.validation.networks.integer")),
						(v: number) => ValidationRules.between(v, 2, 50, $t("components.iqrfnet.network-manager.autonetwork.validation.networks.between")),
					]'
					:min='2'
					:max='50'
					required
				/>
				<INumberInput
					v-model='params.overlappingNetworks!.network'
					:label='$t("components.iqrfnet.network-manager.autonetwork.form.bondingControl.overlappingNetworks.network")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.autonetwork.validation.network.required")),
						(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.network-manager.autonetwork.validation.network.integer")),
						(v: number) => ValidationRules.between(v, 1, 50, $t("components.iqrfnet.network-manager.autonetwork.validation.network.between")),
					]'
					:min='1'
					:max='50'
					required
				/>
				<v-checkbox
					v-model='useNodes'
					:label='$t("components.iqrfnet.network-manager.autonetwork.form.stopConditions.useNodes")'
					density='compact'
					:hide-details='!useNodes'
				/>
			</div>
			<v-divider class='my-2' />
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.autonetwork.form.hwpidFiltering.title') }}
			</v-card-title>
			<v-checkbox
				v-model='useHwpidFiltering'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.hwpidFiltering.use")'
				density='compact'
				:hint='$t("components.iqrfnet.network-manager.autonetwork.notes.hwpidFilter")'
				persistent-hint
			/>
			<ITextInput
				v-if='useHwpidFiltering'
				v-model='hwpidFilter'
				class='mt-2'
				:label='$t("components.iqrfnet.network-manager.autonetwork.form.hwpidFiltering.list")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("components.iqrfnet.network-manager.autonetwork.validation.hwpidFilter.required")),
					(v: string) => hwpidValidator(v, $t("components.iqrfnet.network-manager.autonetwork.validation.hwpidFilter.format")),
				]'
				:description='$t("components.iqrfnet.network-manager.autonetwork.notes.hwpidFilterFormat")'
				required
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Custom'
					:icon='mdiPlay'
					:loading='componentState === ComponentState.Action'
					:text='$t("components.iqrfnet.network-manager.autonetwork.actions.run")'
					:disabled='!isValid.value'
					@click='runAutonetwork()'
				/>
			</template>
		</ICard>
	</v-form>
	<AutonetworkResultDialog
		ref='resultDialog'
		@update-devices='emit("updateDevices")'
	/>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { BondingService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { AutonetworkMidList, IqmeshAutonetworkParams } from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, IDataTable, IDataTableAction, INumberInput, ISelectInput, ITextInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiPlay } from '@mdi/js';
import { ref, Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { VForm } from 'vuetify/components';

import AutonetworkResultDialog from '@/components/iqrfnet/network-manager/AutonetworkResultDialog.vue';
import MidForm from '@/components/iqrfnet/network-manager/MidForm.vue';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';

enum NodeCondition {
	Total = 0,
	New = 1,
}

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const form: Ref<VForm | null> = ref(null);
const params: Ref<IqmeshAutonetworkParams> = ref({
	actionRetries: 1,
	discoveryTxPower: 7,
	discoveryBeforeStart: false,
	skipDiscoveryEachWave: false,
	unbondUnrespondingNodes: false,
	skipPrebonding: false,
	stopConditions: {
		waves: 10,
		emptyWaves: 2,
		numberOfTotalNodes: 239,
		numberOfNewNodes: 239,
		abortOnTooManyNodesFound: false,
	},
	addressSpace: [],
	midFiltering: false,
	midList: [],
	overlappingNetworks: {
		networks: 2,
		network: 1,
	},
	hwpidFiltering: [],
});
const useNodes: Ref<boolean> = ref(false);
const nodeCondition: Ref<NodeCondition> = ref(NodeCondition.Total);
const nodeConditionOptions = [
	{
		title: i18n.t('components.iqrfnet.network-manager.autonetwork.form.stopConditions.nodeConditions.total'),
		value: NodeCondition.Total,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.autonetwork.form.stopConditions.nodeConditions.new'),
		value: NodeCondition.New,
	},
];
const useAddressSpace: Ref<boolean> = ref(false);
const addressSpace: Ref<string> = ref('');
const useMidList: Ref<boolean> = ref(false);
const midHeaders = [
	{
		key: 'deviceMID',
		title: i18n.t('components.iqrfnet.network-manager.autonetwork.midlist.mid'),
		width: '40%',
	},
	{
		key: 'deviceAddr',
		title: i18n.t('components.iqrfnet.common.deviceAddr'),
		width: '40%',
	},
	{
		key: 'actions',
		title: i18n.t('common.columns.actions'),
		align: 'end',
		sortable: false,
	},
];
const useOverlappingNetworks: Ref<boolean> = ref(false);
const useHwpidFiltering: Ref<boolean> = ref(false);
const hwpidFilter: Ref<string> = ref('');
const msgId: Ref<string | null> = ref(null);
const resultDialog: Ref<typeof AutonetworkResultDialog | null> = ref(null);
const emit = defineEmits<{
	updateDevices: [],
}>();

const hwpidValidator = (v: string, error: string): string|true => {
	if (/\s/.test(v) || v.endsWith(',') || /,,/.test(v)) {
		return error;
	}
	const tokens = v.split(',');
	for (const token of tokens) {
		try {
			const num = Number.parseInt(token);
			if (Number.isNaN(num)) {
				return error;
			}
			if (num < 0 || num > 65_535) {
				return error;
			}
		} catch {
			return error;
		}
	}
	return true;
};

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			if (rsp.mType === IqmeshServiceMessages.Autonetwork) {
				handleAutonetwork(rsp);
			}
		});
	}
});

function parseHwpidFilter(input: string): number[] {
	const hwpids = new Set<number>();
	const tokens = input.split(',');
	for (const token of tokens) {
		const num = Number.parseInt(token);
		if (!hwpids.has(num)) {
			hwpids.add(num);
		}
	}
	return [...hwpids];
}

async function runAutonetwork(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const atnwParams = structuredClone(toRaw(params.value));
	// node conditions
	if (!useNodes.value) {
		delete atnwParams.stopConditions.numberOfNewNodes;
		delete atnwParams.stopConditions.numberOfTotalNodes;
	} else if (nodeCondition.value === NodeCondition.Total) {
		delete atnwParams.stopConditions.numberOfNewNodes;
	} else {
		delete atnwParams.stopConditions.numberOfTotalNodes;
	}
	// address space
	if (!useAddressSpace.value) {
		atnwParams.addressSpace = [];
	}
	// mid list
	if (!useMidList.value) {
		delete atnwParams.midFiltering;
		delete atnwParams.midList;
	}
	// overlapping networks
	if (!useOverlappingNetworks.value) {
		delete atnwParams.overlappingNetworks;
	}
	// hwpid filtering
	if (!useHwpidFiltering.value) {
		atnwParams.hwpidFiltering = [];
	} else {
		atnwParams.hwpidFiltering = parseHwpidFilter(hwpidFilter.value);
	}
	resultDialog.value?.open();
	const opts = new DaemonMessageOptions(null);
	msgId.value = await daemonStore.sendMessage(
		BondingService.autonetwork(
			{},
			atnwParams,
			opts,
		),
	);
}

function handleAutonetwork(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		autonetworkProgress(rsp.data.rsp);
		if (rsp.data.rsp.lastWave) {
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
		}
		return;
	}
	daemonStore.removeMessage(msgId.value);
	autonetworkError(rsp.data.statusStr ?? '');
}

function autonetworkProgress(response: Record<string, any>): void {
	const code = response.waveStateCode;
	if (code < 0) {
		autonetworkError(response.waveState);
		return;
	}
	const progress = response.progress;
	const totalNodes = response.nodesNr ?? null;
	const newNodes = response.newNodesNr ?? null;
	const lastWave = response.lastWave ?? false;
	resultDialog.value?.updateProgress(response.wave, lastWave, progress, response.waveState, totalNodes, newNodes);
}

function autonetworkError(error: string): void {
	componentState.value = ComponentState.Idle;
	const translation = i18n.t('components.iqrfnet.network-manager.autonetwork.result.errorMessage', { message: error });
	resultDialog.value?.stopProgress(translation);
}

</script>
