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
			{{ $t('pages.accessControl.apiKeys.title') }}
		</template>
		<template #titleActions>
			<ApiKeyForm
				:action='Action.Add'
				:disabled='componentState === ComponentState.Reloading'
				:role-list='roles'
				@refresh='getKeys()'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:disabled='componentState === ComponentState.Action'
				:tooltip='$t("components.accessControl.apiKeys.actions.reload")'
				@click='getAllData()'
			/>
		</template>
		<IDataTable
			:headers='headers'
			:items='keys'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			:no-data-text='noDataText'
			:hover='true'
			:dense='true'
		>
			<template #item.expiration='{ item }'>
				{{ formatTime(item.expiration) }}
			</template>
			<template #item.role='{ item }'>
				<RoleBadge v-if='getRoleById(item.roleId)' :role='getRoleById(item.roleId)!' />
			</template>
			<template #item.state='{ item }'>
				{{ getState(item.state) }}
			</template>
			<template #item.actions='{ item }'>
				<ApiKeyRevokeDialog
					:api-key='item'
					@revoke='getKeys()'
				/>
				<ApiKeyForm
					:action='Action.Edit'
					:api-key='toRaw(item)'
					:disabled='componentState === ComponentState.Reloading'
					:role-list='roles'
					@refresh='getKeys()'
				/>
				<ApiKeyDeleteDialog
					:api-key='toRaw(item)'
					:disabled='componentState === ComponentState.Reloading'
					@refresh='getKeys()'
				/>
			</template>
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { type ApiKeyService, RoleService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import { type ApiKeyInfo, RoleInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
} from '@iqrf/iqrf-vue-ui';
import { DateTime } from 'luxon';
import { computed, onMounted, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import ApiKeyDeleteDialog from '@/components/access-control/api-keys/ApiKeyDeleteDialog.vue';
import ApiKeyForm from '@/components/access-control/api-keys/ApiKeyForm.vue';
import { useApiClient } from '@/services/ApiClient';
import { useLocaleStore } from '@/store/locale';

import RoleBadge from '../roles/RoleBadge.vue';

import ApiKeyRevokeDialog from './ApiKeyRevokeDialog.vue';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const localeStore = useLocaleStore();
const keyService: ApiKeyService = useApiClient().getSecurityServices().getApiKeyService();
const roleService: RoleService = useApiClient().getSecurityServices().getRoleService();
const headers = computed(() => [
	{ key: 'id', title: i18n.t('common.columns.id') },
	{ key: 'description', title: i18n.t('common.columns.description') },
	{ key: 'expiration', title: i18n.t('components.accessControl.apiKeys.expiration') },
	{ key: 'role', title: i18n.t('components.accessControl.apiKeys.role') },
	{ key: 'state', title: i18n.t('components.accessControl.apiKeys.stateTitle') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
]);
const keys: Ref<ApiKeyInfo[]> = ref([]);
const roles: Ref<RoleInfo[]> = ref([]);

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return 'components.accessControl.apiKeys.noData.fetchError';
	}
	return 'components.accessControl.apiKeys.noData.empty';
});

/**
 * Fetches the roles from backend.
 */
async function getRoles(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		roles.value = await roleService.list();
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.accessControl.roles.actions.list.failure'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

/**
 * Fetches the API keys from backend.
 */
async function getKeys(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		keys.value = await keyService.list();
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.accessControl.apiKeys.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

/**
 * Fetches both roles and keys from the backend.
 */
async function getAllData(): Promise<void> {
	await getRoles();
	if (componentState.value === ComponentState.Error) {
		return;
	}
	await getKeys();
}

/**
 * Returns time formated according to the currently used locales.
 * @param {DateTime|null} time Time object to format (or null)
 * @return {string|null} Formated time as string (or null when no time was specified)
 */
function formatTime(time: DateTime | null): string|null {
	if (time === null) {
		return null;
	}
	return time.setLocale(localeStore.getLocale).toLocaleString(DateTime.DATETIME_FULL_WITH_SECONDS);
}

/**
 * Returns the key state translation / locale as string
 * @param {string} state Key state
 * @return {string} Key state description in selected language
 */
function getState(state: string | null): string {
	switch (state) {
		case 'revoked':
			return i18n.t('components.accessControl.apiKeys.state.revoked');
		default:
			return i18n.t('components.accessControl.apiKeys.state.active');
	}
}

/**
 * Selects role object with given ID in the list or all roles.
 * @param {number} roleId ID of object to select
 * @return {RoleInfo|undefined} Role object with given ID or undefined when object with given ID is not found
 */
function getRoleById(roleId: number): RoleInfo | undefined {
	return roles.value.find((role: RoleInfo) => role.id === roleId);
}

onMounted(() => {
	getAllData();
});
</script>
