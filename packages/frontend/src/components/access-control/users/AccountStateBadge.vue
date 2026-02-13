<!--
Copyright 2024-2026 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   https://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
-->

<template>
	<v-chip
		:color='color'
		:prepend-icon='display.mdAndUp.value ? icon : undefined'
	>
		<span v-if='display.mdAndUp.value'>
			{{ text }}
		</span>
		<v-tooltip
			v-else
			location='start'
		>
			<template #activator='{ props }'>
				<v-icon v-bind='props' :icon='icon' />
			</template>
			{{ text }}
		</v-tooltip>
	</v-chip>
</template>

<script lang='ts' setup>
import { AccountState } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiCheck, mdiEmail, mdiHelp, mdiLock } from '@mdi/js';
import { computed, ComputedRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDisplay } from 'vuetify';

const componentProps = defineProps<{
	/// Account state
	state: AccountState;
}>();
const display = useDisplay();
const i18n = useI18n();
/// Account state color
const color: ComputedRef<string> = computed((): string => {
	const data: Record<AccountState, string> = {
		[AccountState.Blocked]: 'red',
		[AccountState.Invited]: 'blue',
		[AccountState.Verified]: 'green',
		[AccountState.Unverified]: 'orange',
	};
	return data[componentProps.state] ?? 'grey';
});

/// Account state icon
const icon: ComputedRef<string> = computed((): string => {
	const data: Record<AccountState, string> = {
		[AccountState.Blocked]: mdiLock,
		[AccountState.Invited]: mdiEmail,
		[AccountState.Verified]: mdiCheck,
		[AccountState.Unverified]: mdiHelp,
	};
	return data[componentProps.state] ?? mdiHelp;
});

/// Account state text
const text: ComputedRef<string> = computed((): string => {
	const data: Record<AccountState, string> = {
		[AccountState.Blocked]: i18n.t('components.accessControl.users.states.blocked'),
		[AccountState.Invited]: i18n.t('components.accessControl.users.states.invited'),
		[AccountState.Verified]: i18n.t('components.accessControl.users.states.verified'),
		[AccountState.Unverified]: i18n.t('components.accessControl.users.states.unverified'),
	};
	return data[componentProps.state] ?? '';
});
</script>
