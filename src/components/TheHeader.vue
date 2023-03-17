<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<v-app-bar
		fixed
		app
		dark
		color='#3c4b64'
		height='60'
	>
		<v-app-bar-nav-icon @click.stop='$store.commit("sidebar/toggleVisibility")' />
		<v-spacer />
		<v-menu
			v-if='$store.getters["user/isLoggedIn"]'
			bottom
			left
			offset-y
		>
			<template #activator='{ on, attrs }'>
				<v-btn
					v-bind='attrs'
					id='user-menu-button'
					color='white'
					text
					v-on='on'
				>
					{{ $store.getters['user/getName'] }}
					<v-icon
						class='hidden-sm-and-down'
						size='14'
					>
						mdi-chevron-down
					</v-icon>
				</v-btn>
			</template>
			<v-list dense>
				<v-list-item dense to='/profile'>
					<v-list-item-icon>
						<v-icon dense>
							mdi-account
						</v-icon>
					</v-list-item-icon>
					<v-list-item-title>{{ $t('core.profile.title') }}</v-list-item-title>
				</v-list-item>
				<v-divider />
				<v-list-item dense @click='signOut'>
					<v-list-item-icon>
						<v-icon dense>
							mdi-logout
						</v-icon>
					</v-list-item-icon>
					<v-list-item-title>{{ $t('core.sign.out.title') }}</v-list-item-title>
				</v-list-item>
			</v-list>
		</v-menu>
	</v-app-bar>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import ThemeManager from '@/helpers/themeManager';

@Component({})

/**
 * Header component
 */
export default class TheHeader extends Vue {

	/**
	 * Returns the app title
	 * @return {string} App title
	 */
	get title(): string {
		return this.$t(ThemeManager.getTitleKey()).toString();
	}

	/**
	 * User signout method, redirects to the signin page
	 */
	private signOut(): void {
		this.$store.dispatch('user/signOut')
			.then(() => {
				this.$router.push('/sign/in');
				this.$toast.success(this.$t('core.sign.out.message').toString());
			});
	}
}
</script>
