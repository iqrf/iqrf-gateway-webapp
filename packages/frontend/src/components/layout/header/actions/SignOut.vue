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
	<v-list-item
		v-if='mobile'
		:prepend-icon='mdiLogout'
		:title='$t("components.auth.sign.out.title")'
		@click='signOut'
	/>
	<v-btn
		v-else
		v-tooltip:bottom='$t("components.auth.sign.out.title")'
		:icon='mdiLogout'
		@click='signOut'
	/>
</template>

<script setup lang='ts'>
import { mdiLogout } from '@mdi/js';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useUserStore } from '@/store/user';

/// Component props
withDefaults(
	defineProps<{
		mobile?: boolean;
	}>(),
	{
		mobile: false,
	},
);
const i18n = useI18n();
const userStore = useUserStore();

/**
 * Sign out the user
 */
function signOut(): void {
	userStore.signOut();
	toast.success(i18n.t('components.auth.sign.out.message'));
}
</script>
