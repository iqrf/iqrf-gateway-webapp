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
