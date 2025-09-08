<template>
	<v-menu v-if='mobile'>
		<template #activator='{ props }'>
			<v-btn
				v-bind='props'
				:icon='mdiAccountDetails'
			/>
		</template>
		<v-list density='compact'>
			<UserProfile v-if='isLoggedIn' mobile />
			<LocaleSelect mobile />
			<ThemeSwitch mobile />
			<SignOut v-if='isLoggedIn' mobile />
		</v-list>
	</v-menu>
	<div v-else>
		<LocaleSelect />
		<ThemeSwitch />
		<UserProfile v-if='isLoggedIn' />
		<SignOut v-if='isLoggedIn' />
	</div>
</template>

<script lang='ts' setup>
import { mdiAccountDetails } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { useDisplay } from 'vuetify';

import LocaleSelect from '@/components/layout/header/actions/LocaleSelect.vue';
import SignOut from '@/components/layout/header/actions/SignOut.vue';
import ThemeSwitch from '@/components/layout/header/actions/ThemeSwitch.vue';
import UserProfile from '@/components/layout/header/actions/UserProfile.vue';
import { useUserStore } from '@/store/user';

const { mobile } = useDisplay();

const userStore = useUserStore();
const { isLoggedIn } = storeToRefs(userStore);
</script>
