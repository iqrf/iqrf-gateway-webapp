<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
			{{ $t('pages.accessControl.users.title') }}
		</template>
		<template #titleActions>
			<UserForm
				:action='Action.Add'
				:disabled='componentState === ComponentState.Reloading'
				@refresh='getUsers()'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:tooltip='$t("components.accessControl.users.actions.refresh")'
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
			<template #item.role='{ item }'>
				<UserRoleColumn :role='item.role' />
			</template>
			<template #item.language='{ item }'>
				<UserLanguageColumn :language='item.language' />
			</template>
			<template #item.state='{ item }'>
				<UserStateColumn :state='item.state' />
			</template>
			<template #item.actions='{ item }'>
				<UserForm
					:action='Action.Edit'
					:user-info='toRaw(item)'
					:disabled='componentState === ComponentState.Reloading'
					@refresh='getUsers()'
				/>
				<UserDeleteDialog
					:user='toRaw(item)'
					:only-user='users.length === 1'
					:disabled='componentState === ComponentState.Reloading'
					@refresh='getUsers()'
				/>
			</template>
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { type UserInfo } from '@iqrf/iqrf-gateway-webapp-client/types';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
} from '@iqrf/iqrf-vue-ui';
import { computed, onMounted, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import UserDeleteDialog from '@/components/access-control/users/UserDeleteDialog.vue';
import UserForm from '@/components/access-control/users/UserForm.vue';
import UserLanguageColumn from '@/components/access-control/users/UserLanguageColumn.vue';
import UserRoleColumn from '@/components/access-control/users/UserRoleColumn.vue';
import UserStateColumn from '@/components/access-control/users/UserStateColumn.vue';
import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service = useApiClient().getSecurityServices().getUserService();
const headers = [
	{ key: 'username', title: i18n.t('components.common.fields.username') },
	{ key: 'email', title: i18n.t('components.accessControl.users.email') },
	{ key: 'role', title: i18n.t('components.accessControl.users.role') },
	{ key: 'language', title: i18n.t('components.accessControl.users.language') },
	{ key: 'state', title: i18n.t('components.accessControl.users.state') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const users: Ref<UserInfo[]> = ref([]);

onMounted(() => {
	getUsers();
});

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return 'components.accessControl.users.noData.fetchError';
	}
	return 'components.accessControl.users.noData.empty';
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
			i18n.t('components.accessControl.users.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

</script>
