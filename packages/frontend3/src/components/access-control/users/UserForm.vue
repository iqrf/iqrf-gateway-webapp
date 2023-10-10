<template>
	<v-dialog
		v-model='showDialog'
		persistent
		scrollable
		no-click-animation
		:width='width'
	>
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
		<v-form ref='form' @submit.prevent='onSubmit'>
			<Card>
				<template #title>
					<v-icon>{{ headerIcon() }}</v-icon>
					{{ $t(`components.accessControl.users.actions.${action}`) }}
				</template>
				<TextInput
					v-model='user.username'
					:label='$t("components.accessControl.users.username")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.accessControl.users.validation.username")),
					]'
					required
					:prepend-inner-icon='mdiAccount'
				/>
				<TextInput
					v-model='user.email'
					:label='$t("components.accessControl.users.email")'
					:rules='[
						(v: string) => v !== null && v.length > 0 ? ValidationRules.email(v, $t("components.accessControl.users.validation.emailInvalid")) : true,
					]'
					:prepend-inner-icon='mdiEmail'
				/>
				<PasswordInput
					v-if='action === FormAction.Add'
					v-model='(user as UserEdit).password'
					:label='$t("components.accessControl.users.password")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.accessControl.users.validation.password")),
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
				<SelectInput
					v-model='user.language'
					:items='languages'
					:label='$t("components.accessControl.users.language")'
					:prepend-inner-icon='mdiTranslate'
				/>
				<template #actions>
					<v-btn
						color='primary'
						type='submit'
						variant='elevated'
					>
						{{ $t(`generic.actions.${action}`) }}
					</v-btn>
					<v-spacer />
					<v-btn
						color='grey-darken-2'
						variant='elevated'
						@click='close'
					>
						{{ $t('generic.button.cancel') }}
					</v-btn>
				</template>
			</Card>
		</v-form>
	</v-dialog>
</template>

<script lang='ts' setup>
import { UserCreate, UserEdit, UserInfo, UserLanguage, UserRole } from '@iqrf/iqrf-gateway-webapp-client/types/User';
import { AxiosError } from 'axios';
import { ref, Ref, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { useUserStore } from '@/store/user';

import Card from '@/components/Card.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import SelectInput from '@/components/SelectInput.vue';
import TextInput from '@/components/TextInput.vue';


import { basicErrorToast } from '@/helpers/errorToast';
import { getModalWidth } from '@/helpers/modal';
import { getFilteredRoleOptions, getLanguageOptions } from '@/helpers/userData';
import ValidationRules from '@/helpers/ValidationRules';

import { useApiClient } from '@/services/ApiClient';
import { validateForm } from '@/helpers/validateForm';
import { mdiAccount, mdiAccountBadge, mdiAccountEdit, mdiAccountPlus, mdiEmail, mdiKey, mdiPencil, mdiTranslate } from '@mdi/js';
import { FormAction } from '@/enums/controls';

interface Props {
	action: FormAction;
	userInfo?: UserInfo
}

const i18n = useI18n();
const emit = defineEmits(['refresh']);
const props = defineProps<Props>();
const showDialog: Ref<boolean> = ref(false);
const width = getModalWidth();
const form: Ref<typeof VForm | null> = ref(null);
const userStore = useUserStore();
const defaultUser: UserCreate | UserEdit = {
	username: '',
	email: '',
	role: UserRole.Normal,
	language: UserLanguage.English,
};
const user: Ref<UserCreate | UserEdit> = ref(defaultUser);
const languages = getLanguageOptions();
const roles = getFilteredRoleOptions(userStore.getRole as UserRole);

function iconColor(): string {
	if (props.action == FormAction.Add) {
		return 'white';
	}
	return 'info';
}

function activatorIcon(): string {
	if (props.action == FormAction.Add) {
		return mdiAccountPlus;
	}
	return mdiPencil;
}

function headerIcon(): string {
	if (props.action == FormAction.Add) {
		return mdiAccountPlus;
	}
	return mdiAccountEdit;
}

watchEffect(async (): Promise<void> => {
	if (props.action == FormAction.Add) {
		user.value = {...defaultUser, password: ''} as UserCreate;
	} else if (props.action == FormAction.Edit) {
		if (props.userInfo) {
			user.value = {
				username: props.userInfo.username,
				email: props.userInfo.email,
				role: props.userInfo.role,
				language: props.userInfo.language,
			};
		} else {
			user.value = {...defaultUser};
		}
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	const service = useApiClient().getUserService();
	if (props.action == FormAction.Add) {
		service.create(user.value as UserCreate)
			.then(() => onSuccess(user.value))
			.catch((error: AxiosError) => onFailure(error, user.value));
	} else {
		if (props.userInfo?.id === undefined) {
			return;
		}
		service.edit(props.userInfo.id, user.value as UserEdit)
			.then(() => {
				onSuccess(user.value);
				if (props.userInfo?.id === userStore.getId) {
					userStore.refreshUserInfo();
				}
			})
			.catch((error: AxiosError) => onFailure(error, user.value));
	}
}

function onSuccess(entity: UserCreate | UserEdit): void {
	toast.success(
		i18n.t(`components.accessControl.users.messages.${props.action}.success`, {user: entity.username})
	);
	close();
	emit('refresh');
}

function onFailure(error: AxiosError, entity: UserCreate | UserEdit): void {
	basicErrorToast(error, `components.accessControl.users.messages.${props.action}.failure`, {user: entity.username});
}

function close(): void {
	showDialog.value = false;
}
</script>
