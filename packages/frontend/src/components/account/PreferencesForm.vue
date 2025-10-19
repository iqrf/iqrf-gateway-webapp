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
				{{ $t('components.account.preferences.title') }}
			</template>
			<TimeFormatPreferenceInput v-model='preferences.timeFormat' />
			<ThemePreferenceInput v-model='preferences.theme' />
			<template #actions>
				<IActionBtn
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
import { AccountService } from '@iqrf/iqrf-gateway-webapp-client/services';
import {
	UserPreferences,
	UserThemePreference,
	UserTimeFormatPreference,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
} from '@iqrf/iqrf-vue-ui';
import { onMounted, ref, type Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import ThemePreferenceInput
	from '@/components/account/ThemePreferenceInput.vue';
import TimeFormatPreferenceInput
	from '@/components/account/TimeFormatPreferenceInput.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Internationalization instance
const i18n = useI18n();
/// User store
const userStore = useUserStore();
/// Account service
const service: AccountService = useApiClient().getAccountService();
/// Form reference
const form: Ref<VForm | null> = useTemplateRef('form');
/// User preferences
const preferences: Ref<UserPreferences> = ref({
	timeFormat: UserTimeFormatPreference.Auto,
	theme: UserThemePreference.Auto,
});

onMounted(() => getUserPreferences());

/**
 * Retrieves the user preferences
 */
function getUserPreferences(): void {
	const pref = userStore.getUserPreferences;
	if (pref !== null) {
		preferences.value = pref;
	}
}

/**
 * Updates the user preferences
 */
async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	try {
		await service.updatePreferences(preferences.value);
		await userStore.refreshUserPreferences();
		getUserPreferences();
		componentState.value = ComponentState.Ready;
		toast.success(
			i18n.t('components.account.preferences.messages.save.success'),
		);
	} catch {
		componentState.value = ComponentState.Ready;
		toast.error(
			i18n.t('components.account.preferences.messages.save.failed'),
		);
	}
}
</script>
