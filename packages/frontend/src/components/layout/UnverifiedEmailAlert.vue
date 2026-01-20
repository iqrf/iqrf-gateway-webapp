<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

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
		v-if='isLoggedIn && !isVerified && !closed'
		type='warning'
		variant='tonal'
		class='unverified-email-alert'
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
		<v-btn
			class='alert-close'
			:icon='mdiCloseThick'
			variant='text'
			size='small'
			density='compact'
			aria-label='Close'
			@click='closeAlert'
		/>
	</v-alert>
</template>

<script lang='ts' setup>
import { ComponentState } from '@iqrf/iqrf-vue-ui';
import { mdiCloseThick, mdiEmailFast } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { ref, Ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import UrlBuilder from '@/helpers/urlBuilder';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

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

/// Warning was closed by user
const closed = ref(false);
/// Reopen warning on next login / app reload
watch(
	isLoggedIn,
	(loggedIn) => {
		if (!loggedIn) {
			closed.value = false;
		}
	},
);

function closeAlert(): void {
	closed.value = true;
}

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
.unverified-email-alert {
	position: relative;
}

.alert {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding-right: 28px;
}

.alert-close {
	position: absolute;
	top: 6px;
	right: 6px;
	min-width: 0;
	height: 28px;
	width: 28px;
	color: currentColor;
	opacity: 0.6;
	transition: opacity 0.2s ease-in-out;

	&:hover,
	&:focus {
		opacity: 1;
	}
}
</style>
