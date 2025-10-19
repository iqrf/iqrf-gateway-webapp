<template>
	<v-form
		:disabled='componentState === ComponentState.Action'
	>
		<ICard flat>
			<v-card-title>
				{{ $t('components.iqrfnet.network-manager.network-issues.title') }}
			</v-card-title>
			<ISelectInput
				v-model='issue'
				:label='$t("components.iqrfnet.network-manager.network-issues.issue")'
				:items='issueOptions'
				:description='issueNote'
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Download'
					:loading='componentState === ComponentState.Action'
					:text='$t("components.iqrfnet.network-manager.network-issues.actions.resolve")'
					@click='resolveIssue()'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { MaintenanceService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, ISelectInput } from '@iqrf/iqrf-vue-ui';
import { computed, onBeforeUnmount, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useDaemonStore } from '@/store/daemonSocket';

enum IssueType {
	InconsistentMids = 0,
	DuplicateAddresses = 1,
	UselessPrebondedNodes = 2,
}

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const issue: Ref<IssueType> = ref(IssueType.InconsistentMids);
const issueOptions = [
	{
		title: i18n.t('components.iqrfnet.network-manager.network-issues.issues.inconsistentMids'),
		value: IssueType.InconsistentMids,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.network-issues.issues.duplicatedAddresses'),
		value: IssueType.DuplicateAddresses,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.network-issues.issues.prebondedNodes'),
		value: IssueType.UselessPrebondedNodes,
	},
];
const msgId: Ref<string | null> = ref(null);

const issueNote = computed(() => {
	if (issue.value === IssueType.DuplicateAddresses) {
		return i18n.t('components.iqrfnet.network-manager.network-issues.note.duplicatedAddresses');
	} else if (issue.value === IssueType.InconsistentMids) {
		return i18n.t('components.iqrfnet.network-manager.network-issues.note.inconsistentMidsInCoordinator');
	} else {
		return i18n.t('components.iqrfnet.network-manager.network-issues.note.uselessPrebondedNodes');
	}
});

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			switch (rsp.mType) {
				case IqmeshServiceMessages.MaintenanceDuplicatedAddresses:
					handleDuplicatedAddresses(rsp);
					break;
				case IqmeshServiceMessages.MaintenanceInconsistentMids:
					handleInconsistentMids(rsp);
					break;
				case IqmeshServiceMessages.MaintenanceUselessPrebondedNodes:
					handlePrebondedNodes(rsp);
					break;
			}
		});
	}
});

function resolveIssue(): void {
	componentState.value = ComponentState.Action;
	if (issue.value === IssueType.DuplicateAddresses) {
		resolveDuplicatedAddresses();
	} else if (issue.value === IssueType.InconsistentMids) {
		resolveInconsistentMids();
	} else {
		resolvePrebondedNodes();
	}
}

async function resolveDuplicatedAddresses(): Promise<void> {
	const opts = new DaemonMessageOptions(
		null,
		505_000,
		i18n.t('components.iqrfnet.network-manager.network-issues.messages.duplicatedAddresses.timeout'),
		() => {
			componentState.value = ComponentState.Idle;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		MaintenanceService.duplicatedAddresses(
			{ repeat: 1, returnVerbose: true },
			opts,
		),
	);
}

function handleDuplicatedAddresses(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		successToast(
			i18n.t('components.iqrfnet.network-manager.network-issues.messages.duplicatedAddresses.success'),
		);
	} else if (rsp.data.status === 1_003) {
		infoToast(
			i18n.t('common.messages.noNodes'),
		);
	} else {
		errorToast(
			i18n.t('components.iqrfnet.network-manager.network-issues.messages.duplicatedAddresses.failed'),
		);
	}
}

async function resolveInconsistentMids(): Promise<void> {
	const opts = new DaemonMessageOptions(
		null,
		1_620_000,
		i18n.t('components.iqrfnet.network-manager.network-issues.messages.inconsistentMids.timeout'),
		() => {
			componentState.value = ComponentState.Idle;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		MaintenanceService.inconsistentMids(
			{ repeat: 1, returnVerbose: true },
			opts,
		),
	);
}

function handleInconsistentMids(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		successToast(
			i18n.t('components.iqrfnet.network-manager.network-issues.messages.inconsistentMids.success'),
		);
	} else if (rsp.data.status === 1_003) {
		infoToast(
			i18n.t('common.messages.noNodes'),
		);
	} else {
		errorToast(
			i18n.t('components.iqrfnet.network-manager.network-issues.messages.inconsistentMids.failed'),
		);
	}
}

async function resolvePrebondedNodes(): Promise<void> {
	const opts = new DaemonMessageOptions(
		null,
		15_000,
		i18n.t('components.iqrfnet.network-manager.network-issues.messages.prebondedNodes.timeout'),
		() => {
			componentState.value = ComponentState.Idle;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		MaintenanceService.unusedPrebondedNodes(
			{ repeat: 1, returnVerbose: true },
			opts,
		),
	);
}

function handlePrebondedNodes(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		successToast(
			i18n.t('components.iqrfnet.network-manager.network-issues.messages.prebondedNodes.success'),
		);
	} else if (rsp.data.status === 1_003) {
		infoToast(
			i18n.t('common.messages.noNodes'),
		);
	} else {
		errorToast(
			i18n.t('components.iqrfnet.network-manager.network-issues.messages.prebondedNodes.failed'),
		);
	}
}

function successToast(message: string): void {
	toast.success(message);
}

function infoToast(message: string): void {
	toast.info(message);
}

function errorToast(message: string): void {
	toast.error(message);
}

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
