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
	<ModalWindow v-model='showDialog'>
		<template #activator='{ props }'>
			<CardTitleActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
				:tooltip='$t("components.accessControl.users.actions.add")'
			/>
			<DataTableAction
				v-else
				v-bind='props'
				:action='action'
				:tooltip='$t("components.accessControl.users.actions.edit")'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Saving'
			@submit.prevent='onSubmit()'
		>
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
					v-model='(user as UserEdit).password'
					:label='$t("components.common.fields.password")'
					:rules='[
						(v: string) => v.length === 0 || ValidationRules.betweenLen(v, 15, 64, $t("components.common.validations.password.betweenLen")),
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
				</PasswordInput>
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
						:disabled='!isValid.value || componentState === ComponentState.Saving'
						type='submit'
					/>
					<v-spacer />
					<CardActionBtn
						:action='Action.Cancel'
						:disabled='componentState === ComponentState.Saving'
						@click='close()'
					/>
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
import { ValidationRules } from '@iqrf/iqrf-vue-ui';
import {
	mdiAccount,
	mdiAccountBadge,
	mdiEmail,
	mdiHelpCircleOutline,
	mdiKey,
} from '@mdi/js';
import { ref, type Ref, watch } from 'vue';
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
import { getFilteredRoleOptions } from '@/helpers/userData';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

interface Props {
	action: Action;
	userInfo?: UserInfo
}

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const i18n = useI18n();
const service = useApiClient().getSecurityServices().getUserService();
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

watch(showDialog, (newVal: boolean): void => {
	if (!newVal) {
		return;
	}
	if (componentProps.action === Action.Add) {
		user.value = { ...defaultUser, password: '' } as UserCreate;
	} else if (componentProps.action === Action.Edit) {
		if (componentProps.userInfo) {
			user.value = {
				username: componentProps.userInfo.username,
				email: componentProps.userInfo.email,
				role: componentProps.userInfo.role,
				language: componentProps.userInfo.language,
				password: '',
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
	componentState.value = ComponentState.Saving;
	const params = { ...user.value };
	try {
		if (componentProps.action === Action.Add) {
			await service.create(params as UserCreate);
		} else {
			if (componentProps.userInfo?.id === undefined) {
				return;
			}
			if (params.password?.length === 0) {
				delete params.password;
			}
			await service.update(componentProps.userInfo.id, params as UserEdit);
			if (componentProps.userInfo.id === userStore.getId) {
				userStore.refreshUserInfo();
			}
		}
		toast.success(
			i18n.t(`components.accessControl.users.messages.${componentProps.action}.success`, { user: user.value.username }),
		);
		close();
		emit('refresh');
	} catch {
		toast.error(
			i18n.t(`components.accessControl.users.messages.${componentProps.action}.failed`, { user: user.value.username }),
		);
	}
	componentState.value = ComponentState.Ready;
}

function close(): void {
	if (componentProps.action === Action.Add) {
		user.value = { ...defaultUser };
	}
	showDialog.value = false;
}
</script>
