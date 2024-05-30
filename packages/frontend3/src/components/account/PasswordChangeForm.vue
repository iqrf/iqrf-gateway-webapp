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
	<v-form ref='form' v-slot='{ isValid }' @submit.prevent='onSubmit'>
		<Card>
			<template #title>
				{{ $t('account.profile.password.title') }}
			</template>
			<PasswordInput
				v-model='passwordChange.old'
				:label='$t("account.profile.password.current")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("account.profile.password.validation.current")),
				]'
				required
				:prepend-inner-icon='mdiKey'
			/>
			<PasswordInput
				v-model='passwordChange.new'
				:label='$t("account.profile.password.new")'
				:rules='[
					(v: string|null) => ValidationRules.required(v, $t("account.profile.password.validation.new")),
				]'
				required
				:prepend-inner-icon='mdiKey'
			/>
			<template #actions>
				<CardActionBtn
					:action='Action.Edit'
					:disabled='!isValid.value'
					type='submit'
				/>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type UserPasswordChange } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiKey } from '@mdi/js';
import { type AxiosError } from 'axios';
import { ref, type Ref } from 'vue';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import PasswordInput from '@/components/layout/form/PasswordInput.vue';
import UrlBuilder from '@/helpers/urlBuilder';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';

const form: Ref<typeof VForm | null> = ref(null);
const passwordChange: Ref<UserPasswordChange> = ref<UserPasswordChange>({
	old: '',
	new: '',
	baseUrl: new UrlBuilder().getBaseUrl(),
});

/// @todo add messages
async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	useApiClient().getAccountService().changePassword(passwordChange.value)
		.then(() => {
			toast.success('Success');
		})
		.catch((error: AxiosError) => {
			toast.error('Error: ' + error.message);
		});
}
</script>
