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
	<v-progress-circular
		v-if='componentState === ComponentState.Action'
		color='primary'
		class='me-2'
		:indeterminate='true'
		:size='21'
	/>
	<IDataTableAction
		v-else
		:action='user.state === AccountState.Blocked ? Action.Unblock : Action.Block'
		@click='user.state === AccountState.Blocked ? unblockUser() : blockUser()'
	/>
</template>

<script lang='ts' setup>
import { AccountState, UserInfo } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Action, ComponentState, IDataTableAction } from '@iqrf/iqrf-vue-ui';
import { computed, ComputedRef, ref, Ref } from 'vue';
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
const translationParams: ComputedRef<Record<string, string>> = computed((): Record<string, string> => ({
	name: componentProps.user.username,
}));
const userService = useApiClient().getSecurityServices().getUserService();

/**
 * Blocks the user
 */
async function blockUser(): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		await userService.block(componentProps.user.id);
		close();
		emit('changed');
		toast.success(i18n.t(
			'components.accessControl.users.messages.block.success',
			translationParams.value,
		));
	} catch {
		toast.error(i18n.t(
			'components.accessControl.users.messages.block.failed',
			translationParams.value,
		));
	} finally {
		componentState.value = ComponentState.Ready;
	}
}

/**
 * Unblocks the user
 */
async function unblockUser(): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		await userService.unblock(componentProps.user.id);
		close();
		emit('changed');
		toast.success(i18n.t(
			'components.accessControl.users.messages.unblock.success',
			translationParams.value,
		));
	} catch {
		toast.error(i18n.t(
			'components.accessControl.users.messages.unblock.failed',
			translationParams.value,
		));
	} finally {
		componentState.value = ComponentState.Ready;
	}
}
</script>
