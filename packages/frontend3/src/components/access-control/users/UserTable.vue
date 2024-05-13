<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
			<UserForm :action='FormAction.Add' @refresh='getUsers' />
			<v-btn
				color='white'
				:icon='mdiReload'
				@click='getUsers'
			/>
		</template>
		<DataTable
			:headers='headers'
			:items='users'
			:loading='loading'
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
				<UserForm :action='FormAction.Edit' :user-info='toRaw(item)' @refresh='getUsers' />
				<UserDeleteDialog :user='toRaw(item)' :only-user='users.length === 1' @refresh='getUsers' />
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type UserInfo } from '@iqrf/iqrf-gateway-webapp-client/types/User';
import { mdiReload } from '@mdi/js';
import { onMounted, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';

import UserDeleteDialog from '@/components/access-control/users/UserDeleteDialog.vue';
import UserForm from '@/components/access-control/users/UserForm.vue';
import UserLanguageColumn from '@/components/access-control/users/UserLanguageColumn.vue';
import UserRoleColumn from '@/components/access-control/users/UserRoleColumn.vue';
import UserStateColumn from '@/components/access-control/users/UserStateColumn.vue';
import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';
import { FormAction } from '@/enums/controls';
import { useApiClient } from '@/services/ApiClient';

const i18n = useI18n();

const loading: Ref<boolean> = ref(false);
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

function getUsers(): void {
	loading.value = true;
	useApiClient().getUserService().list().then((rsp: UserInfo[]) => {
		users.value = rsp;
		loading.value = false;
	});
}

</script>
