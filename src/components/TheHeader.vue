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
	<CHeader :fixed='false' color-scheme='dark'>
		<CToggler
			in-header
			class='ml-3 d-lg-none'
			@click='$store.commit("sidebar/toggleSidebarMobile")'
		/>
		<CToggler
			in-header
			class='ml-3 d-md-down-none'
			@click='$store.commit("sidebar/toggleSidebarDesktop")'
		/>
		<CHeaderBrand class='ml-auto d-lg-none' to='/'>
			<Logo :alt='title' />
		</CHeaderBrand>
		<CHeaderNav class='ml-auto mr-3'>
			<CDropdown
				v-if='$store.getters["user/isLoggedIn"]'
				id='user-menu-button'
				:in-nav='true'
				class='c-header-nav-items'
				placement='bottom-end'
				add-menu-classes='pt-0 pb-0'
			>
				<template #toggler>
					<CHeaderNavLink class='dropdown-toggle'>
						{{ $store.getters['user/getName'] }}
					</CHeaderNavLink>
				</template>
				<CDropdownItem to='/profile'>
					<CIcon :content='cilUser' />
					{{ $t('core.profile.title') }}
				</CDropdownItem>
				<CDropdownItem @click='signOut'>
					<CIcon :content='cilLockLocked' />
					{{ $t('core.sign.out.title') }}
				</CDropdownItem>
			</CDropdown>
		</CHeaderNav>
	</CHeader>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {
	CBadge,
	CDropdown,
	CHeader,
	CHeaderBrand,
	CHeaderNav,
	CHeaderNavLink,
	CIcon,
	CToggler,
} from '@coreui/vue/src';
import {cilLockLocked, cilUser} from '@coreui/icons';
import ThemeManager from '@/helpers/themeManager';

@Component({
	components: {
		CBadge,
		CDropdown,
		CHeader,
		CHeaderBrand,
		CHeaderNav,
		CHeaderNavLink,
		CIcon,
		CToggler,
		Logo: ThemeManager.getSidebarLogo(),
	},
	data: () => ({
		cilLockLocked,
		cilUser,
	}),
})

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
