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
		<div class='badge-group'>
			<span style='color: white;'>
				{{ $t('daemonStatus.mode') }}
				<CBadge
					:color='daemonBadgeColor'
				>
					{{ $t('daemonStatus.modes.' + (isSocketConnected ? daemonStatus.mode : 'unknown')) }}
				</CBadge>
			</span>
			<span style='color: white;'>
				{{ $t('daemonStatus.websocket.title') }}
				<CBadge
					:color='isSocketConnected ? "success": "danger"'
				>
					{{ $t('daemonStatus.websocket.' + (isSocketConnected ? 'connected' : 'notConnected')) }}
				</CBadge>
			</span> 
		</div>
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
import { mapGetters } from 'vuex';

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
	},
	computed: {
		...mapGetters({
			daemonStatus: 'daemonStatus',
			isSocketConnected: 'isSocketConnected',
		}),
	},
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
	 * Computes Daemon mode badge color
	 * @returns {string} Daemon mode badge color
	 */
	get daemonBadgeColor(): string {
		const daemonStatus = this.$store.getters.daemonStatus;
		const socketConnected = this.$store.getters.isSocketConnected;
		if (!socketConnected) {
			return 'secondary';
		}
		if (daemonStatus.mode === 'unknown') {
			return 'secondary';
		} else if (daemonStatus.mode === 'operational' ||
			daemonStatus.mode === 'forwarding') {
			return 'success';
		} else {
			return 'danger';
		}
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

<style scoped>
.badge-group {
	display: flex;
	flex-grow: 1;
	flex-direction: column;
	position: relative;
	align-self: center;
}
</style>
