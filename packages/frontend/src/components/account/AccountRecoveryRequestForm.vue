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
		<ICard>
			<template #title>
				{{ $t('pages.account.recovery.title') }}
			</template>
			<v-alert
				v-if='componentState === ComponentState.Success'
				:text='$t("components.account.recovery.request.messages.success")'
				type='success'
			/>
			<div v-else>
				{{ $t('components.account.recovery.request.prompt') }}
				<ITextInput
					v-model='data.username'
					class='mt-4'
					:label='$t("common.labels.username")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("common.validation.username.required")),
					]'
					required
					:prepend-inner-icon='mdiAccount'
				/>
				<IActionBtn
					color='primary'
					container-type='card'
					:disabled='!isValid.value'
					:loading='componentState === ComponentState.Action'
					:icon='mdiAccountKey'
					:text='$t("components.account.recovery.request.button")'
					type='submit'
				/>
			</div>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { AccountService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { UserAccountRecovery } from '@iqrf/iqrf-gateway-webapp-client/types';
import {
	ComponentState,
	IActionBtn,
	ICard,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiAccount, mdiAccountKey } from '@mdi/js';
import { AxiosError } from 'axios';
import { ref, type Ref, type TemplateRef, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import UrlBuilder from '@/helpers/urlBuilder';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Account service
const service: AccountService = useApiClient().getAccountService();
/// Internationalization instance
const i18n = useI18n();
/// Form reference
const form: TemplateRef<VForm> = useTemplateRef('form');
/// Account recovery request
const data: Ref<UserAccountRecovery> = ref({
	username: '',
	baseUrl: new UrlBuilder().getBaseUrl(),
});

/**
 * Request password recovery
 */
async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	try {
		await service.requestPasswordRecovery(data.value);
		toast.success(
			i18n.t('components.account.recovery.request.messages.success'),
		);
		componentState.value = ComponentState.Success;
	} catch (error) {
		componentState.value = ComponentState.Idle;
		if (!(error instanceof AxiosError)) {
			toast.error(
				i18n.t('components.account.recovery.request.messages.failure'),
			);
			return;
		}
		switch (error?.response?.status) {
			case 403:
				toast.error(
					i18n.t('components.account.recovery.request.messages.notVerified'),
				);
				break;
			case 404:
				toast.error(
					i18n.t('components.account.recovery.request.messages.notFound'),
				);
				break;
			default:
				toast.error(
					i18n.t('components.account.recovery.request.messages.failure'),
				);
		}
	}
}
</script>
