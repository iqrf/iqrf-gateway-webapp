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
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='componentState === ComponentState.Action'
		@submit.prevent='onSubmit'
	>
		<ICard :bottom-margin='true'>
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
						:label='$t("components.common.fields.email")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("components.common.validations.email.required")),
							(v: string) => ValidationRules.email(v, $t("components.common.validations.email.email")),
						]'
						required
						:prepend-inner-icon='mdiEmail'
					/>
					<ILanguageSelect v-model='user.language' />
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<IActionBtn
					v-if='[ComponentState.Ready, ComponentState.Action].includes(componentState)'
					:action='Action.Save'
					container-type='card'
					:disabled='!isValid.value'
					:loading='componentState === ComponentState.Action'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { type AccountService } from '@iqrf/iqrf-gateway-webapp-client/services';
import {
	type UserEdit,
	type UserInfo,
	UserRole,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	ILanguageSelect,
	ITextInput,
	Language,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiAccount, mdiEmail } from '@mdi/js';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import UrlBuilder from '@/helpers/urlBuilder';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

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
	language: Language.English,
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
	componentState.value = ComponentState.Action;
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
