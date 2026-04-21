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
				:action='Action.Invite'
				:disabled='componentState === ComponentState.Reloading'
				:role-list="roles"
				@refresh='getUsers()'
			/>
			<UserForm
				:action='Action.Add'
				:disabled='componentState === ComponentState.Reloading'
				:role-list='roles'
				@refresh='getUsers()'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:tooltip='$t("components.accessControl.users.actions.refresh")'
				@click='getData()'
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
				<RoleBadge
					v-if='getRoleById(item.roleId)'
					:role='getRoleById(item.roleId)!'
				/>
			</template>
			<template #item.language='{ item }'>
				<ILanguageFlag :language='item.language' />
			</template>
			<template #item.state='{ item }'>
				<AccountStateBadge :state='item.state' />
			</template>
			<template #item.actions='{ item }'>
				<ResendEmailButton
					:user='item'
					@changed='getUsers()'
				/>
				<AccountStateButton
					v-if='userId !== item.id'
					:user='item'
					@changed='getUsers()'
				/>
				<UserForm
					:action='Action.Edit'
					:user-info='toRaw(item)'
					:disabled='componentState === ComponentState.Reloading'
					:role-list='roles'
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
import { RoleInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
	ILanguageFlag,
} from '@iqrf/iqrf-vue-ui';
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import AccountStateBadge from '@/components/access-control/users/AccountStateBadge.vue';
import AccountStateButton from '@/components/access-control/users/AccountStateButton.vue';
import ResendEmailButton from '@/components/access-control/users/ResendEmailButton.vue';
import UserDeleteDialog from '@/components/access-control/users/UserDeleteDialog.vue';
import UserForm from '@/components/access-control/users/UserForm.vue';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

import RoleBadge from '../roles/RoleBadge.vue';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const userStore = useUserStore();
const { getId: userId } = storeToRefs(userStore);
const userService = useApiClient().getSecurityServices().getUserService();
const roleService = useApiClient().getSecurityServices().getRoleService();
const headers = computed(() => [
	{ key: 'username', title: i18n.t('components.common.fields.username') },
	{ key: 'email', title: i18n.t('components.accessControl.users.email') },
	{ key: 'role', title: i18n.t('components.accessControl.users.role') },
	{ key: 'language', title: i18n.t('components.accessControl.users.language') },
	{ key: 'state', title: i18n.t('components.accessControl.users.state') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
]);
const users: Ref<UserInfo[]> = ref([]);
const roles: Ref<RoleInfo[]> = ref([]);

onMounted(() => {
	getData();
});

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return 'components.accessControl.users.noData.fetchError';
	}
	return 'components.accessControl.users.noData.empty';
});

function getRoleById(roleId: number): RoleInfo | undefined {
	return roles.value.find((role: RoleInfo) => role.id === roleId);
}

async function getUsers(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		users.value = await userService.list();
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.accessControl.users.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

async function getData(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		users.value = await userService.list();
	} catch {
		toast.error(
			i18n.t('components.accessControl.users.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
		return;
	}
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

</script>
