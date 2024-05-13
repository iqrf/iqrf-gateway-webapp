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
	<v-btn
		to='/profile'
		:prepend-icon='mdiAccount'
	>
		{{ userStore.getName }}
	</v-btn>
	<v-btn
		:icon='mdiLogout'
		@click='signOut'
	/>
</template>

<script lang='ts' setup>
import { mdiAccount, mdiLogout } from '@mdi/js';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useUserStore } from '@/store/user';

const i18n = useI18n();
const userStore = useUserStore();

function signOut(): void {
	userStore.signOut();
	toast.success(
		i18n.t('auth.sign.out.message').toString(),
	);
}
</script>
