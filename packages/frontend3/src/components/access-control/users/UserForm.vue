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
	<ModalWindow v-model='showDialog'>
		<template #activator='{ props }'>
			<CardTitleActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
			/>
			<DataTableAction
				v-else
				v-bind='props'
				:action='action'
			/>
		</template>
		<v-form ref='form' v-slot='{ isValid }' @submit.prevent='onSubmit'>
			<Card>
				<template #title>
					{{ $t(`components.accessControl.users.actions.${action}`) }}
				</template>
				<TextInput
					v-model='user.username'
					:label='$t("components.common.fields.username")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.common.validations.username.required")),
					]'
					required
					:prepend-inner-icon='mdiAccount'
				/>
				<TextInput
					v-model='user.email'
					:label='$t("components.accessControl.users.email")'
					:rules='[
						(v: string) => v !== null && v.length > 0 ? ValidationRules.email(v, $t("components.accessControl.users.validations.email.email")) : true,
					]'
					:prepend-inner-icon='mdiEmail'
				/>
				<PasswordInput
					v-if='action === Action.Add'
					v-model='(user as UserEdit).password'
					:label='$t("components.accessControl.users.password")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.accessControl.users.validations.password.required")),
					]'
					required
					:prepend-inner-icon='mdiKey'
				/>
				<SelectInput
					v-model='user.role'
					:items='roles'
					:label='$t("components.accessControl.users.role")'
					:prepend-inner-icon='mdiAccountBadge'
				/>
				<LanguageInput v-model='user.language' />
				<template #actions>
					<CardActionBtn
						:action='action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<CardActionBtn :action='Action.Cancel' @click='close' />
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import {
	type UserCreate,
	type UserEdit,
	type UserInfo,
	UserLanguage,
	UserRole,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import {
	mdiAccount,
	mdiAccountBadge,
	mdiEmail,
	mdiKey,
} from '@mdi/js';
import { type AxiosError } from 'axios';
import { ref, type Ref, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import LanguageInput from '@/components/account/LanguageInput.vue';
import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import PasswordInput from '@/components/layout/form/PasswordInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { basicErrorToast } from '@/helpers/errorToast';
import { getFilteredRoleOptions } from '@/helpers/userData';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';
import { Action } from '@/types/Action';

interface Props {
	action: Action;
	userInfo?: UserInfo
}

const i18n = useI18n();
const emit = defineEmits(['refresh']);
const componentProps = defineProps<Props>();
const showDialog: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
const userStore = useUserStore();
const defaultUser: UserCreate | UserEdit = {
	username: '',
	email: '',
	role: UserRole.Normal,
	language: UserLanguage.English,
};
const user: Ref<UserCreate | UserEdit> = ref(defaultUser);
const roles = getFilteredRoleOptions(userStore.getRole!);


watchEffect((): void => {
	if (componentProps.action === Action.Add) {
		user.value = { ...defaultUser, password: '' } as UserCreate;
	} else if (componentProps.action === Action.Edit) {
		if (componentProps.userInfo) {
			user.value = {
				username: componentProps.userInfo.username,
				email: componentProps.userInfo.email,
				role: componentProps.userInfo.role,
				language: componentProps.userInfo.language,
			};
		} else {
			user.value = { ...defaultUser };
		}
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	const service = useApiClient().getUserService();
	if (componentProps.action === Action.Add) {
		service.create(user.value as UserCreate)
			.then(() => onSuccess(user.value))
			.catch((error: AxiosError) => onFailure(error, user.value));
	} else {
		if (componentProps.userInfo?.id === undefined) {
			return;
		}
		service.update(componentProps.userInfo.id, user.value as UserEdit)
			.then(() => {
				onSuccess(user.value);
				if (componentProps.userInfo?.id === userStore.getId) {
					userStore.refreshUserInfo();
				}
			})
			.catch((error: AxiosError) => onFailure(error, user.value));
	}
}

function onSuccess(entity: UserCreate | UserEdit): void {
	toast.success(
		i18n.t(`components.accessControl.users.messages.${componentProps.action}.success`, { user: entity.username }),
	);
	close();
	emit('refresh');
}

function onFailure(error: AxiosError, entity: UserCreate | UserEdit): void {
	basicErrorToast(error, `components.accessControl.users.messages.${componentProps.action}.failure`, { user: entity.username });
}

function close(): void {
	showDialog.value = false;
}
</script>
