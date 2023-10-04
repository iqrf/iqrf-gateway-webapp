<template>
	<Card>
		<template #title>
			{{ $t('pages.management.users.title') }}
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
				<span>
					<UserForm :action='FormAction.Edit' :user-info='toRaw(item)' @refresh='getUsers' />
					<v-tooltip
						activator='parent'
						location='bottom'
					>
						{{ $t('components.management.users.actions.edit') }}
					</v-tooltip>
				</span>
				<span>
					<UserDeleteDialog :user='toRaw(item)' :only-user='users.length === 1' @refresh='getUsers' />
					<v-tooltip
						activator='parent'
						location='bottom'
					>
						{{ $t('components.management.users.actions.delete') }}
					</v-tooltip>
				</span>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { UserInfo } from '@iqrf/iqrf-gateway-webapp-client/types/User';
import { onMounted, ref, Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';

import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';
import UserDeleteDialog from '@/components/management/users/UserDeleteDialog.vue';
import UserForm from '@/components/management/users/UserForm.vue';
import UserLanguageColumn from '@/components/management/users/UserLanguageColumn.vue';
import UserRoleColumn from '@/components/management/users/UserRoleColumn.vue';
import UserStateColumn from '@/components/management/users/UserStateColumn.vue';

import { FormAction } from '@/enums/controls';
import { useApiClient } from '@/services/ApiClient';
import { mdiReload } from '@mdi/js';

const i18n = useI18n();

const loading: Ref<boolean> = ref(false);
const headers = [
	{key: 'username', title: i18n.t('components.management.users.username').toString()},
	{key: 'email', title: i18n.t('components.management.users.email').toString()},
	{key: 'role', title: i18n.t('components.management.users.role').toString()},
	{key: 'language', title: i18n.t('components.management.users.language').toString()},
	{key: 'state', title: i18n.t('components.management.users.state').toString()},
	{key: 'actions', title: i18n.t('common.columns.actions').toString(), align: 'end'}
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
