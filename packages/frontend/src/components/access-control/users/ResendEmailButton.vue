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
	<span v-if='user.email !== null && [AccountState.Invited, AccountState.Unverified].includes(user.state)'>
		<v-progress-circular
			v-if='componentState === ComponentState.Action'
			color='primary'
			class='me-2'
			:indeterminate='true'
			:size='21'
		/>
		<IDataTableAction
			v-else
			:action='Action.Resend'
			@click='resend()'
		/>
	</span>
</template>

<script lang='ts' setup>
import { AccountState, UserInfo } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Action, ComponentState, IDataTableAction } from '@iqrf/iqrf-vue-ui';
import { ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';

const componentProps = defineProps<{
	/// User info
	user: UserInfo;
}>();
const emit = defineEmits<{
	changed: [];
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const i18n = useI18n();
const userService = useApiClient().getSecurityServices().getUserService();

/**
 * Blocks the user
 */
async function resend(): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		await userService.resendVerificationEmail(componentProps.user.id);
		close();
		emit('changed');
		toast.success(i18n.t('components.account.verification.messages.requestSuccess'));
	} catch {
		toast.error(i18n.t('components.account.verification.messages.requestFailure'));
	} finally {
		componentState.value = ComponentState.Ready;
	}
}
</script>
