<template>
	<div>
		<h1>{{ $t('maintenance.mender.title') }}</h1>
		<CCard body-wrapper>
			<CListGroup>
				<CListGroupItem
					v-if='roleIdx <= roles.admin'
					to='/maintenance/mender/service/'
				>
					<header class='list-group-item-heading'>
						{{ $t('maintenance.mender.service.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('maintenance.mender.service.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='roleIdx <= roles.admin'
					to='/maintenance/mender/update/'
				>
					<header class='list-group-item-heading'>
						{{ $t('maintenance.mender.update.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('maintenance.mender.update.description') }}
					</p>
				</CListGroupItem>
			</CListGroup>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

import {getRoleIndex} from '@/helpers/user';
import {NavigationGuardNext, Route} from 'vue-router';

@Component({
	components: {
		CCard,
		CListGroup,
		CListGroupItem,
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('mender')) {
				vm.$toast.error(vm.$t('maintenance.disabled').toString());
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'maintenance.mender.title',
	},
})

/**
 * Mender disambiguation component
 */
export default class MenderDisambiguation extends Vue {
	/**
	 * @var {number} roleIdx Index of role in user role enum
	 */
	private roleIdx = 0;

	/**
	 * @constant {Record<string, number>} roles Dictionary of role indices
	 */
	private roles: Record<string, number> = {
		admin: 0,
		normal: 1,
		basicadmin: 2,
		basic: 3,
	};

	/**
	 * Retrieves user role and calculates the role index
	 */
	private created(): void {
		const roleVal = this.$store.getters['user/getRole'];
		this.roleIdx = getRoleIndex(roleVal);
	}
}
</script>
