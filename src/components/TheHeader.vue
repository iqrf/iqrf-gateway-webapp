<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
			<LogoBig :alt='$t("core.title")' />
		</CHeaderBrand>
		<CHeaderNav class='ml-auto mr-3'>
			<CDropdown
				v-if='$store.getters["user/isLoggedIn"]'
				:in-nav='true'
				class='c-header-nav-items'
				placement='bottom-end'
				add-menu-classes='pt-0'
			>
				<template #toggler>
					<CHeaderNavLink class='dropdown-toggle'>
						{{ $store.getters['user/getName'] }}
					</CHeaderNavLink>
				</template>
				<CDropdownItem @click='signOut'>
					<CIcon :content='icons.logout' />
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
import LogoBig from '../assets/logo-big.svg';
import {cilLockLocked} from '@coreui/icons';
import { Dictionary } from 'vue-router/types/router';

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
		LogoBig,
	}
})

/**
 * Header component
 */
export default class TheHeader extends Vue {
	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI Icons
	 */
	private icons: Dictionary<Array<string>> = {
		logout: cilLockLocked
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
