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
	<v-alert
		v-if='isLoggedIn && !isVerified'
		type='warning'
		variant='tonal'
	>
		<div v-if='hasEmail' style='display: flex; justify-content: space-between; align-items: center;'>
			{{ $t('account.verification.unverified', {email: userEmail}) }}
			<v-btn
				color='warning'
				size='small'
				dense
				:prepend-icon='mdiEmailFast'
				@click='resend'
			>
				{{ $t('account.verification.resend') }}
			</v-btn>
		</div>
		<div v-else style='display: flex; justify-content: space-between; align-items: center;'>
			{{ $t('account.verification.missing') }}
			<v-btn
				color='warning'
				size='small'
				to='/profile'
				dense
				:prepend-icon='mdiEmailFast'
			>
				{{ $t('account.verification.addEmail') }}
			</v-btn>
		</div>
	</v-alert>
</template>

<script lang='ts' setup>
import { mdiEmailFast } from '@mdi/js';
import { type AxiosError } from 'axios';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { basicErrorToast } from '@/helpers/errorToast';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

const i18n = useI18n();
const userStore = useUserStore();
const { hasEmail, isLoggedIn, isVerified, getEmail: userEmail } = storeToRefs(userStore);

function resend(): void {
	const userId = userStore.getId;
	if (userId === null) {
		return;
	}
	useApiClient().getUserService().resendVerificationEmail(userId)
		.then(() => {
			toast.success(
				i18n.t('core.account.verification.messages.requestSuccess').toString(),
			);
		})
		.catch((error: AxiosError) => {
			basicErrorToast(error, 'core.account.verification.messages.requestFailure');
		});
}

</script>
