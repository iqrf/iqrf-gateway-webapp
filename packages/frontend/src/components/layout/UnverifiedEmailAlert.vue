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
	<v-alert
		v-if='isLoggedIn && !isVerified'
		type='warning'
		variant='tonal'
	>
		<div v-if='hasEmail' class='alert'>
			{{ $t('components.account.verification.unverified', { email: userEmail }) }}
			<v-btn
				color='warning'
				size='small'
				dense
				:loading='componentState === ComponentState.Loading'
				:prepend-icon='mdiEmailFast'
				@click='resend'
			>
				{{ $t('components.account.verification.resend') }}
			</v-btn>
		</div>
		<div v-else class='alert'>
			{{ $t('components.account.verification.missing') }}
			<v-btn
				color='warning'
				size='small'
				to='/profile'
				dense
				:prepend-icon='mdiEmailFast'
			>
				{{ $t('components.account.verification.addEmail') }}
			</v-btn>
		</div>
	</v-alert>
</template>

<script lang='ts' setup>
import { mdiEmailFast } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import UrlBuilder from '@/helpers/urlBuilder';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';
import { ComponentState } from '@/types/ComponentState';

/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
/// Internationalization instance
const i18n = useI18n();
/// User store
const userStore = useUserStore();
const {
	hasEmail,
	isLoggedIn,
	isVerified,
	getEmail: userEmail,
} = storeToRefs(userStore);

/**
 * Resends the verification e-mail
 */
async function resend(): Promise<void> {
	componentState.value = ComponentState.Loading;
	try {
		await useApiClient().getAccountService().resendVerificationEmail({
			baseUrl: new UrlBuilder().getBaseUrl(),
		});
		toast.success(
			i18n.t('components.account.verification.messages.requestSuccess'),
		);
	} catch {
		toast.error(i18n.t('components.account.verification.messages.requestFailure'));
	} finally {
		componentState.value = ComponentState.Idle;
	}
}

</script>

<style scoped lang='scss'>
.alert {
	display: flex;
	justify-content: space-between;
	align-items: center;
}
</style>
