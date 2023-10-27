<template>
	<v-card>
		<v-card-title class='text-center'>
			{{ $t('account.recovery.title') }}
		</v-card-title>
		<v-card-text>
			<div v-if='!success'>
				{{ $t('account.recovery.prompt') }}
				<v-form ref='form' class='mt-4' @submit.prevent='onSubmit'>
					<TextInput
						v-model='request.username'
						:label='$t("user.username")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("user.validation.username")),
						]'
						required
						:prepend-inner-icon='mdiAccount'
					/>
					<v-btn
						color='primary'
						type='submit'
					>
						{{ $t('account.recovery.button') }}
					</v-btn>
				</v-form>
			</div>
			<div v-else>
				{{ $t('account.recovery.success') }}
			</div>
		</v-card-text>
	</v-card>
</template>

<script lang='ts' setup>
import { type AccountService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { type UserAccountRecovery } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiAccount } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import TextInput from '@/components/TextInput.vue';
import { basicErrorToast } from '@/helpers/errorToast';
import UrlBuilder from '@/helpers/urlBuilder';
import {validateForm} from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';

const i18n = useI18n();
const request: Ref<UserAccountRecovery> = ref({
	baseUrl: (new UrlBuilder()).getBaseUrl(),
	username: '',
});
const success: Ref<boolean> = ref(false);
const form: Ref<typeof VForm | null> = ref(null);
const service: AccountService = useApiClient().getAccountService();

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	service.requestPasswordRecovery(request.value)
		.then(() => {
			toast.success(
				i18n.t('account.recovery.messages.success').toString(),
			);
			success.value = true;
		})
		.catch((error) => {
			basicErrorToast(error, 'account.recovery.messages.failure');
		});
}
</script>
