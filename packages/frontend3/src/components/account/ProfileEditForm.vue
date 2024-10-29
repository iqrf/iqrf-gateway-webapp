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
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='componentState === ComponentState.Saving'
		@submit.prevent='onSubmit'
	>
		<Card :bottom-margin='true'>
			<template #title>
				{{ $t('components.account.details.title') }}
			</template>
			<v-alert
				v-if='componentState === ComponentState.FetchFailed'
				type='error'
				variant='tonal'
				:text='$t("components.common.messages.fetchFailed")'
			/>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading@3, button'
			>
				<v-responsive>
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
						:label='$t("components.common.fields.email")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("components.common.validations.email.required")),
							(v: string) => ValidationRules.email(v, $t("components.common.validations.email.email")),
						]'
						required
						:prepend-inner-icon='mdiEmail'
					/>
					<LanguageInput v-model='user.language' />
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<CardActionBtn
					v-if='[ComponentState.Ready, ComponentState.Saving].includes(componentState)'
					:action='Action.Save'
					:disabled='!isValid.value'
					:loading='componentState === ComponentState.Saving'
					type='submit'
				/>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type AccountService } from '@iqrf/iqrf-gateway-webapp-client/services';
import {
	type UserEdit,
	type UserInfo,
	UserLanguage,
	UserRole,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiAccount, mdiEmail } from '@mdi/js';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import LanguageInput from '@/components/account/LanguageInput.vue';
import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import UrlBuilder from '@/helpers/urlBuilder';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Internalization instance
const i18n = useI18n();
/// User store
const userStore = useUserStore();
/// User data
const user: Ref<UserEdit> = ref({
	username: '',
	email: '',
	language: UserLanguage.English,
	role: UserRole.Basic,
});
/// Account service
const accountService: AccountService = useApiClient().getAccountService();

onMounted(async () => await getUserData());

/**
 * Retrieves user profile information
 */
async function getUserData(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		const data: UserInfo = await accountService.getInfo();
		user.value = {
			username: data.username,
			email: data.email,
			language: data.language,
			role: data.role,
			baseUrl: new UrlBuilder().getBaseUrl(),
		};
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.account.details.messages.fetch.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

/**
 * Updates the user profile
 */
async function onSubmit(): Promise<void> {
	componentState.value = ComponentState.Saving;
	try {
		await accountService.update(user.value);
		componentState.value = ComponentState.Ready;
		toast.success(
			i18n.t('components.account.details.messages.save.success'),
		);
		await userStore.refreshUserInfo();
	} catch {
		toast.error(
			i18n.t('components.account.details.messages.save.failed'),
		);
		componentState.value = ComponentState.Ready;
	}
}
</script>
