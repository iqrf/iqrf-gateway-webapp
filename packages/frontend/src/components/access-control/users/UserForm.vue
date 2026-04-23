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
	<IModalWindow
		v-model='showDialog'
		persistent
	>
		<template #activator='{ props }'>
			<IActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
				container-type='card-title'
				:tooltip='$t("components.accessControl.users.actions.add")'
				:disabled='disabled'
			/>
			<IActionBtn
				v-else-if='action === Action.Invite'
				v-bind='props'
				:action='action'
				container-type='card-title'
				:tooltip='$t("components.accessControl.users.actions.invite")'
				:disabled='disabled'
			/>
			<IDataTableAction
				v-else
				v-bind='props'
				:action='action'
				:tooltip='$t("components.accessControl.users.actions.edit")'
				:disabled='disabled'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Action'
			@submit.prevent='onSubmit()'
		>
			<ICard :action='action'>
				<template #title>
					{{ $t(`components.accessControl.users.actions.${action}`) }}
				</template>
				<ITextInput
					v-model='user.username'
					:label='$t("components.common.fields.username")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.common.validations.username.required")),
					]'
					required
					:prepend-inner-icon='mdiAccount'
				/>
				<ITextInput
					v-model='user.email'
					:label='$t("components.accessControl.users.email")'
					:rules='[
						(v: string) => v !== null && v.length > 0
							? ValidationRules.email(v, $t("components.accessControl.users.validations.email.email"))
							: true,
					]'
					:prepend-inner-icon='mdiEmail'
				/>
				<IPasswordInput
					v-if='action !== Action.Invite'
					v-model='(user as UserEdit).password'
					:label='$t("components.common.fields.password")'
					:rules='[
						(v: string) => v.length === 0 || ValidationRules.betweenLen(
							v, 15, 64, $t("components.common.validations.password.betweenLen"),
						),
						(v: string) => v.length === 0 || ValidationRules.webappUserPassword(v, $t("components.common.validations.password.invalid")),
					]'
					:prepend-inner-icon='mdiKey'
					:persistent-hint='action === Action.Edit'
					:hint='action === Action.Edit ? $t("components.accessControl.users.notes.optionalPasswordEdit") : undefined'
				>
					<template #append>
						<v-tooltip location='left'>
							<template #activator='{ props }'>
								<v-icon v-bind='props' :icon='mdiHelpCircleOutline' />
							</template>
							<div style='white-space: pre-line;'>
								{{ $t('components.common.hints.password.note') }}
								<ul style='padding-left: 1.2rem; margin: 0;'>
									<li>{{ $t('components.common.hints.password.letters') }}</li>
									<li>{{ $t('components.common.hints.password.numbers') }}</li>
									<li>{{ $t('components.common.hints.password.special', { special: '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~' }) }}</li>
								</ul>
							</div>
						</v-tooltip>
					</template>
				</IPasswordInput>
				<RoleSelect
					:model-value='role'
					:role-list='componentProps.roleList'
					@update:model-value='selectRole'
				/>
				<ILanguageSelect v-model='user.language' />
				<template #actions>
					<IActionBtn
						:action='action'
						container-type='card'
						:disabled='!isValid.value || componentState === ComponentState.Action'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						container-type='card'
						:disabled='componentState === ComponentState.Action'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import {
	type UserCreate,
	type UserEdit,
	type UserInfo,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { RoleInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { Language } from '@iqrf/iqrf-ui-common-types';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTableAction,
	ILanguageSelect,
	IModalWindow,
	IPasswordInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import {
	mdiAccount,
	mdiEmail,
	mdiHelpCircleOutline,
	mdiKey,
} from '@mdi/js';
import { ref, type Ref, type TemplateRef, useTemplateRef, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import UserRoleInput from '@/components/access-control/users/UserRoleInput.vue';
import UrlBuilder from '@/helpers/urlBuilder';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

import RoleSelect from '../roles/RoleSelect.vue';

const componentProps = defineProps<{
	action: Action.Add | Action.Invite | Action.Edit;
	userInfo?: UserInfo;
	disabled?: boolean;
	roleList: RoleInfo[];
}>();
const emit = defineEmits<{
	refresh: [];
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const i18n = useI18n();
const service = useApiClient().getSecurityServices().getUserService();
const showDialog: Ref<boolean> = ref(false);
const form: TemplateRef<VForm> = useTemplateRef('form');
const userStore = useUserStore();
const defaultUser: UserCreate | UserEdit = {
	username: '',
	email: '',
	roleId: componentProps.roleList.find((role) => role.systemKey === 'normal')?.id ?? -1,
	language: Language.English,
};
const user: Ref<UserCreate | UserEdit> = ref(defaultUser);
const role: Ref<RoleInfo | undefined> = ref(undefined);

watch(showDialog, (newVal: boolean): void => {
	if (!newVal) {
		return;
	}
	if (componentProps.action === Action.Add) {
		user.value = {
			...defaultUser,
			password: '',
			baseUrl: new UrlBuilder().getBaseUrl(),
		} satisfies UserCreate;
	} else if (componentProps.action === Action.Edit) {
		if (componentProps.userInfo) {
			user.value = {
				username: componentProps.userInfo.username,
				email: componentProps.userInfo.email,
				roleId: componentProps.userInfo.roleId,
				language: componentProps.userInfo.language,
				password: '',
			};
		} else {
			user.value = {
				...defaultUser,
				baseUrl: new UrlBuilder().getBaseUrl(),
			} satisfies UserCreate;
		}
	}
	if (!componentProps.roleList) {
		componentState.value = ComponentState.Error;
		// TODO - add error message about failing to retreive role list
		return;
	}
	if (role.value?.id === user.value.roleId) {
		return;
	}
	let defaultRole: RoleInfo | undefined = undefined;
	if (user.value.roleId === -1) {
		defaultRole = componentProps.roleList.find((role) => role.systemKey === 'normal');
	} else {
		defaultRole = componentProps.roleList.find((role) => role.id === user.value.roleId);
	}
	if (!defaultRole) {
		componentState.value = ComponentState.Error;
		// TODO - add error message about failing to retreive role list
		return;
	}
	selectRole(defaultRole);
});

/**
 * Selects new user role
 * @param {RoleInfo} newRole New user role
 */
function selectRole(newRole: RoleInfo | undefined): void {
	user.value.roleId = newRole?.id!;
	role.value = newRole;
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	if (role.value === undefined) {
		// TODO - add error message that the role is required
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...user.value };
	const translationParams = { user: user.value.username };
	try {
		switch (componentProps.action) {
			case Action.Add:
				await service.create(params as UserCreate);
				toast.success(i18n.t(
					'components.accessControl.users.messages.add.success',
					translationParams,
				));
				break;
			case Action.Invite:
				await service.create(params as UserCreate);
				toast.success(i18n.t(
					'components.accessControl.users.messages.invite.success',
					translationParams,
				));
				break;
			case Action.Edit:
				if (componentProps.userInfo?.id === undefined) {
					return;
				}
				if (params.password?.length === 0) {
					delete params.password;
				}
				await service.update(componentProps.userInfo.id, params as UserEdit);
				if (componentProps.userInfo.id === userStore.getId) {
					await userStore.refreshUserInfo();
				}
				toast.success(i18n.t(
					'components.accessControl.users.messages.edit.success',
					translationParams,
				));
				break;
		}
		close();
		emit('refresh');
	} catch {
		switch (componentProps.action) {
			case Action.Add:
				toast.error(i18n.t(
					'components.accessControl.users.messages.add.failed',
					translationParams,
				));
				break;
			case Action.Invite:
				toast.error(i18n.t(
					'components.accessControl.users.messages.invite.failed',
					translationParams,
				));
				break;
			case Action.Edit:
				toast.error(i18n.t(
					'components.accessControl.users.messages.edit.failed',
					translationParams,
				));
				break;
		}
	}
	componentState.value = ComponentState.Ready;
}

/**
 * Closes the dialog window and restores default role if new one was being added.
 */
function close(): void {
	if (componentProps.action === Action.Add) {
		user.value = { ...defaultUser };
	}
	showDialog.value = false;
}
</script>
