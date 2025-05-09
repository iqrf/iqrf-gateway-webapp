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
	<Card>
		<template #title>
			{{ $t('pages.accessControl.users.title') }}
		</template>
		<template #titleActions>
			<UserForm
				:action='Action.Add'
				@refresh='getUsers()'
			/>
			<CardTitleActionBtn
				:action='Action.Reload'
				@click='getUsers()'
			/>
		</template>
		<DataTable
			:headers='headers'
			:items='users'
			:loading='componentState === ComponentState.Loading'
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
				<UserForm :action='Action.Edit' :user-info='toRaw(item)' @refresh='getUsers' />
				<UserDeleteDialog :user='toRaw(item)' :only-user='users.length === 1' @refresh='getUsers' />
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type UserInfo } from '@iqrf/iqrf-gateway-webapp-client/types';
import { onMounted, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';

import UserDeleteDialog from '@/components/access-control/users/UserDeleteDialog.vue';
import UserForm from '@/components/access-control/users/UserForm.vue';
import UserLanguageColumn from '@/components/access-control/users/UserLanguageColumn.vue';
import UserRoleColumn from '@/components/access-control/users/UserRoleColumn.vue';
import UserStateColumn from '@/components/access-control/users/UserStateColumn.vue';
import Card from '@/components/layout/card/Card.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service = useApiClient().getSecurityServices().getUserService();
const headers = [
	{ key: 'username', title: i18n.t('components.accessControl.users.username') },
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

async function getUsers(): Promise<void> {
	componentState.value = ComponentState.Loading;
	try {
		users.value = await service.list();
	} catch {
		//
	}
	componentState.value = ComponentState.Ready;
}

</script>
