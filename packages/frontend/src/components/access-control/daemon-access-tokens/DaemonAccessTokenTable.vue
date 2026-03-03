<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->

<template>
	<ICard>
		<template #title>
			{{ $t('pages.accessControl.daemonAccessTokens.title') }}
		</template>
		<template #titleActions>
			<DaemonAccessTokenForm
				:disabled='componentState === ComponentState.Reloading'
				@refresh='getTokens()'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:disabled='componentState === ComponentState.Action'
				:tooltip='$t("components.accessControl.daemonAccessTokens.actions.reload")'
				@click='getTokens()'
			/>
		</template>
		<IDataTable
			:headers='headers'
			:items='tokens'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			:no-data-text='noDataText'
			:hover='true'
			:dense='true'
		>
			<template #item.created_at='{ item }'>
				{{ formatTime(item.created_at) }}
			</template>
			<template #item.expires_at='{ item }'>
				{{ formatTime(item.expires_at) }}
			</template>
			<template #item.status='{ item }'>
				<DaemonAccessTokenStatus
					:status='item.status'
					:invalidated-at='item.invalidated_at'
				/>
			</template>
			<template #item.actions='{ item }'>
				<IDataTableAction
					v-if='item.status === DaemonApiTokenStatus.Valid'
					:action='Action.Reset'
					color='warning'
					:tooltip='$t("components.accessControl.daemonAccessTokens.actions.rotate")'
					:disabled='componentState === ComponentState.Reloading'
					@click='openRotateDialog(item.id)'
				/>
				<IDataTableAction
					v-if='item.status === DaemonApiTokenStatus.Valid'
					:action='Action.Block'
					:tooltip='$t("components.accessControl.daemonAccessTokens.actions.revoke")'
					:disabled='componentState === ComponentState.Reloading'
					@click='openRevokeDialog(item.id)'
				/>
			</template>
		</IDataTable>
	</ICard>
	<DaemonAccessTokenRotateDialog
		ref='rotateDialog'
		@refresh='getTokens()'
	/>
	<DaemonAccessTokenRevokeDialog
		ref='revokeDialog'
		@refresh='getTokens()'
	/>
</template>

<script lang='ts' setup>
import { DaemonApiTokenService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import { DaemonApiTokenInfo, DaemonApiTokenStatus } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { Action, ComponentState, IActionBtn, ICard, IDataTable, IDataTableAction } from '@iqrf/iqrf-vue-ui';
import { DateTime } from 'luxon';
import { computed, onMounted, ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import DaemonAccessTokenForm from '@/components/access-control/daemon-access-tokens/DaemonAccessTokenForm.vue';
import DaemonAccessTokenRevokeDialog from '@/components/access-control/daemon-access-tokens/DaemonAccessTokenRevokeDialog.vue';
import DaemonAccessTokenRotateDialog from '@/components/access-control/daemon-access-tokens/DaemonAccessTokenRotateDialog.vue';
import DaemonAccessTokenStatus from '@/components/access-control/daemon-access-tokens/DaemonAccessTokenStatus.vue';
import { useApiClient } from '@/services/ApiClient';
import { useLocaleStore } from '@/store/locale';

const componentState = ref<ComponentState>(ComponentState.Created);
const i18n = useI18n();
const localeStore = useLocaleStore();
const service: DaemonApiTokenService = useApiClient()
	.getSecurityServices()
	.getDaemonApiTokenService();
const headers = computed(() => [
	{ key: 'id', title: i18n.t('common.columns.id') },
	{ key: 'owner', title: i18n.t('components.accessControl.daemonAccessTokens.owner') },
	{ key: 'created_at', title: i18n.t('common.labels.createdAt') },
	{ key: 'expires_at', title: i18n.t('common.labels.expiration') },
	{ key: 'status', title: i18n.t('common.labels.status') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
]);
const tokens = ref<DaemonApiTokenInfo[]>([]);
const rotateDialog = useTemplateRef<InstanceType<typeof DaemonAccessTokenRotateDialog>|null>('rotateDialog');
const revokeDialog = useTemplateRef<InstanceType<typeof DaemonAccessTokenRevokeDialog>|null>('revokeDialog');

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return 'components.accessControl.daemonAccessTokens.noData.fetchError';
	}
	return 'components.accessControl.daemonAccessTokens.noData.empty';
});

async function getTokens(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		tokens.value = await service.list();
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.accessControl.daemonAccessTokens.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

function openRotateDialog(id: number): void {
	rotateDialog.value?.open(id);
}

function openRevokeDialog(id: number): void {
	revokeDialog.value?.open(id);
}

function formatTime(time: DateTime | null): string|null {
	if (time === null) {
		return null;
	}
	return time.setLocale(localeStore.getLocale).toLocaleString(DateTime.DATETIME_SHORT_WITH_SECONDS);
}

onMounted(() => {
	getTokens();
});
</script>
