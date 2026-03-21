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
			{{ $t('pages.accessControl.mosquittoUsers.title') }}
		</template>
		<template #titleActions>
			<MosquittoUserForm
				:disabled='componentState === ComponentState.Reloading'
				@refresh='getUsers()'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:disabled='componentState === ComponentState.Action'
				:tooltip='$t("components.accessControl.mosquittoUsers.actions.reload")'
				@click='getUsers()'
			/>
		</template>
		<IDataTable
			:headers='headers'
			:items='users'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			:no-data-text='noDataText'
			:hover='true'
			:dense='true'
		>
			<template #item.createdAt='{ item }'>
				{{ formatTime(item.createdAt) }}
			</template>
			<template #item.state='{ item }'>
				<MosquittoUserTableState
					:state='item.state'
					:blocked-at='item.blockedAt'
				/>
			</template>
			<template #item.actions='{ item }'>
				<IDataTableAction
					v-if='item.state === MosquittoUserState.Active'
					:action='Action.Block'
					:tooltip='$t("components.accessControl.mosquittoUsers.actions.block")'
					:disabled='componentState === ComponentState.Reloading'
					@click='openBlockDialog(item.id)'
				/>
			</template>
		</IDataTable>
	</ICard>
	<MosquittoUserBlockDialog
		ref='blockDialog'
		@refresh='getUsers()'
	/>
</template>

<script lang='ts' setup>
import { MosquittoUserService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import { MosquittoUser, MosquittoUserState } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { Action, ComponentState, IActionBtn, ICard, IDataTable, IDataTableAction } from '@iqrf/iqrf-vue-ui';
import { DateTime } from 'luxon';
import { computed, onMounted, ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import MosquittoUserBlockDialog from '@/components/access-control/mosquitto-users/MosquittoUserBlockDialog.vue';
import MosquittoUserForm from '@/components/access-control/mosquitto-users/MosquittoUserForm.vue';
import MosquittoUserTableState from '@/components/access-control/mosquitto-users/MosquittoUserTableState.vue';
import { useApiClient } from '@/services/ApiClient';
import { useLocaleStore } from '@/store/locale';

const componentState = ref<ComponentState>(ComponentState.Created);
const i18n = useI18n();
const localeStore = useLocaleStore();
const service: MosquittoUserService = useApiClient()
	.getSecurityServices()
	.getMosquittoUserService();
const headers = computed(() => [
	{ key: 'id', title: i18n.t('common.columns.id') },
	{ key: 'username', title: i18n.t('common.labels.username') },
	{ key: 'createdAt', title: i18n.t('common.columns.createdAt') },
	{ key: 'state', title: i18n.t('common.columns.state') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
]);
const users = ref<MosquittoUser[]>([]);
const blockDialog = useTemplateRef<InstanceType<typeof MosquittoUserBlockDialog>|null>('blockDialog');

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return 'components.accessControl.mosquittoUsers.noData.fetchError';
	}
	return 'components.accessControl.mosquittoUsers.noData.empty';
});

async function getUsers(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		users.value = await service.list();
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.accessControl.mosquittoUsers.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

function openBlockDialog(id: number): void {
	blockDialog.value?.open(id);
}

function formatTime(time: DateTime | null): string|null {
	if (time === null) {
		return null;
	}
	return time.setLocale(localeStore.getLocale).toLocaleString(DateTime.DATETIME_SHORT_WITH_SECONDS);
}

onMounted(() => {
	getUsers();
});
</script>
