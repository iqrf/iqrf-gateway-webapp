<template>
	<v-stepper-vertical-item
		:value='componentProps.index'
		:title='$t("components.install.wizard.adminUserPreferences.title")'
	>
		<v-form
			ref='form'
			v-model='formValidity'
			:disabled='componentState === ComponentState.Saving'
		>
			<TimeFormatPreferenceInput v-model='preferences.timeFormat' />
			<ThemePreferenceInput v-model='preferences.theme' />
		</v-form>
		<template #actions='{ next }'>
			<CardActionBtn
				:action='Action.Next'
				:disabled='!formValidity'
				:loading='componentState === ComponentState.Saving'
				@click='onSubmit(next)'
			/>
			<CardActionBtn
				:action='Action.Skip'
				class='ml-2'
				:loading='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
				@click='next'
			/>
		</template>
	</v-stepper-vertical-item>
</template>

<script lang='ts' setup>
import {
	UserPreferences, UserThemePreference,
	UserTimeFormatPreference,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import ThemePreferenceInput
	from '@/components/account/ThemePreferenceInput.vue';
import TimeFormatPreferenceInput
	from '@/components/account/TimeFormatPreferenceInput.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Component props
const componentProps = defineProps({
	index: {
		type: Number,
		required: true,
	},
});
/// User store
const userStore = useUserStore();
/// Form reference
const form: Ref<VForm | null> = ref(null);
/// Form validity
const formValidity: Ref<boolean | null> = ref(null);
/// User preferences
const preferences: Ref<UserPreferences> = ref({
	timeFormat: UserTimeFormatPreference.Auto,
	theme: UserThemePreference.Auto,
});
/// Internationalization instance
const i18n = useI18n();

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
 * @param {Function} onClickNext Next button click handler
 */
async function onSubmit(onClickNext: Function): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Saving;
	try {
		await useApiClient().getAccountService().updatePreferences(preferences.value);
		await userStore.refreshUserPreferences();
		getUserPreferences();
		componentState.value = ComponentState.Ready;
		toast.success(
			i18n.t('components.account.preferences.messages.save.success'),
		);
		componentState.value = ComponentState.Idle;
		onClickNext();
	} catch {
		toast.error(
			i18n.t('components.account.preferences.messages.save.failed'),
		);
		componentState.value = ComponentState.Idle;
	}
}

</script>
