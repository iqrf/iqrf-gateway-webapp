<template>
	<ModalWindow v-model='showDialog'>
		<template #activator='{ props }'>
			<v-btn
				v-if='action === FormAction.Add'
				v-bind='props'
				:color='iconColor()'
				:icon='activatorIcon()'
			/>
			<v-icon
				v-else
				v-bind='props'
				:color='iconColor()'
				class='me-2'
				size='large'
			>
				{{ activatorIcon() }}
			</v-icon>
		</template>
		<v-form ref='form' v-slot='{ isValid }' @submit.prevent='onSubmit'>
			<Card>
				<template #title>
					<v-icon>{{ headerIcon() }}</v-icon>
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
					v-if='action === FormAction.Add'
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
					<FormActionButton
						:action='action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<FormActionButton :action='FormAction.Cancel' @click='close' />
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
} from '@iqrf/iqrf-gateway-webapp-client/types/User';
import {
	mdiAccount,
	mdiAccountBadge,
	mdiAccountEdit,
	mdiAccountPlus,
	mdiEmail,
	mdiKey,
	mdiPencil,
} from '@mdi/js';
import { type AxiosError } from 'axios';
import { ref, type Ref, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import LanguageInput from '@/components/account/LanguageInput.vue';
import Card from '@/components/Card.vue';
import FormActionButton from '@/components/FormActionButton.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import SelectInput from '@/components/SelectInput.vue';
import TextInput from '@/components/TextInput.vue';
import { FormAction } from '@/enums/controls';
import { basicErrorToast } from '@/helpers/errorToast';
import { getFilteredRoleOptions } from '@/helpers/userData';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

interface Props {
	action: FormAction;
	userInfo?: UserInfo
}

const i18n = useI18n();
const emit = defineEmits(['refresh']);
const componentProps = defineProps<Props>();
const showDialog: Ref<boolean> = ref(false);
const form: Ref<typeof VForm | null> = ref(null);
const userStore = useUserStore();
const defaultUser: UserCreate | UserEdit = {
	username: '',
	email: '',
	role: UserRole.Normal,
	language: UserLanguage.English,
};
const user: Ref<UserCreate | UserEdit> = ref(defaultUser);
const roles = getFilteredRoleOptions(userStore.getRole!);

function iconColor(): string {
	if (componentProps.action === FormAction.Add) {
		return 'white';
	}
	return 'info';
}

function activatorIcon(): string {
	if (componentProps.action === FormAction.Add) {
		return mdiAccountPlus;
	}
	return mdiPencil;
}

function headerIcon(): string {
	if (componentProps.action === FormAction.Add) {
		return mdiAccountPlus;
	}
	return mdiAccountEdit;
}

watchEffect(async (): Promise<void> => {
	if (componentProps.action === FormAction.Add) {
		user.value = { ...defaultUser, password: '' } as UserCreate;
	} else if (componentProps.action === FormAction.Edit) {
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
	if (componentProps.action === FormAction.Add) {
		service.create(user.value as UserCreate)
			.then(() => onSuccess(user.value))
			.catch((error: AxiosError) => onFailure(error, user.value));
	} else {
		if (componentProps.userInfo?.id === undefined) {
			return;
		}
		service.edit(componentProps.userInfo.id, user.value as UserEdit)
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
