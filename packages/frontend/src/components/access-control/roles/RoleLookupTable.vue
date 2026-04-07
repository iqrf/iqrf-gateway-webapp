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
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:tooltip='$t("components.accessControl.roles.actions.refresh")'
				@click='getRoles()'
			/>
		</template>
		<IDataTable :headers='headers' :items='roles'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			:no-data-text='noDataText'
			:hover='true'
			:dense='true'>
			<template #item.scopes='{ item }'>
				<ScopeTable
					:selected="item.scopes"
					:disable-edit="true"
				/>
			</template>
			<template #item.actions='{ item }'>
				<IActionBtn
					:text='$t("common.buttons.select")'
					@click='selectRole(item)'
				/>
			</template>
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { Action, ComponentState, IActionBtn, IDataTable } from '@iqrf/iqrf-vue-ui';
import { RoleInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { computed, onMounted, ref, Ref } from 'vue';
import { useApiClient } from '@/services/ApiClient';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';
import ScopeTable from './ScopeTable.vue';

const emit = defineEmits<{
	select: [role: RoleInfo];
}>();

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const roles: Ref<RoleInfo[]> = ref([]);
const service = useApiClient().getSecurityServices().getRoleService();
const i18n = useI18n();
const headers = computed(() => [
	{ key: 'name', title: i18n.t('components.accessScopes.roles.table.name') },
	{ key: 'description', title: i18n.t('components.accessScopes.roles.table.description') },
	{ key: 'system', title: i18n.t('components.accessScopes.roles.table.system') },
	{ key: 'scopes', title: i18n.t('components.accessScopes.roles.table.scopes') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
]);
const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return 'component.accessControl.roles.noData.fetchFailed';
	}
	return 'component.accessControl.roles.noData.empty';
});

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

function selectRole(role: RoleInfo): void {
	emit('select', role);
}

onMounted(() => {
	getRoles();
});

</script>
