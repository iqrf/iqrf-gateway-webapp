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
			{{ $t('components.accessControl.roles.title') }}
		</template>
		<template #titleActions>
			<RoleForm
				:action='Action.Add'
				@update='updateRole'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:tooltip='$t("components.accessControl.roles.actions.refresh")'
				@click='getRoles()'
			/>
		</template>
		<IDataTable
			:headers='headers'
			:items='roles'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			:no-data-text='noDataText'
			:hover='true'
			:dense='true'
		>
			<template #item.actions='{ item }'>
				<RoleForm :action='Action.Edit' :role='item' @update='updateRole' />
				<RoleDeleteDialog :role='item' @delete='deleteRole' />
			</template>
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { RoleInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { Action, ComponentState, IActionBtn, ICard, IDataTable } from '@iqrf/iqrf-vue-ui';
import { computed, onMounted, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';

import RoleDeleteDialog from './RoleDeleteDialog.vue';
import RoleForm from './RoleForm.vue';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const roles: Ref<RoleInfo[]> = ref([]);
const service = useApiClient().getSecurityServices().getRoleService();
const i18n = useI18n();
const headers = computed(() => [
	{ key: 'name', title: i18n.t('components.accessControl.roles.columns.name') },
	{ key: 'description', title: i18n.t('components.accessControl.roles.columns.description') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
]);
const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return 'components.accessControl.roles.noData.fetchFailed';
	}
	return 'components.accessControl.roles.noData.empty';
});

/**
 * Updates role, or adds role to the end of role array when its not in the array
 * @param {RoleInfo} role Role to update or add
 */
function updateRole(role: RoleInfo): void {
	for (let i = 0; i < roles.value.length; i++) {
		if (roles.value[i].id === role.id) {
			roles.value[i] = role;
			return;
		}
	}
	roles.value.push(role);
}

/**
 * Removes role from the array (delete on backend is performed in the component)
 * @param {RoleInfo} id role ID
 */
async function deleteRole(id: number): Promise<void> {
	roles.value = roles.value.filter((v: RoleInfo): boolean => v.id !== id);
}

/**
 * Returns the list of all roles
 */
async function getRoles(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		roles.value = await service.list();
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(i18n.t('components.accessControl.roles.actions.list.failure'));
		componentState.value = ComponentState.FetchFailed;
	}
}

onMounted(() => {
	getRoles();
});

</script>
