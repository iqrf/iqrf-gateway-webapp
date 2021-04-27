<template>
	<div>
		<h1>{{ $t('maintenance.title') }}</h1>
		<CCard body-wrapper>
			<CListGroup>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("pixla")'
					to='/maintenance/pixla/'
				>
					<header class='list-group-item-heading'>
						{{ $t('maintenance.pixla.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('maintenance.pixla.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("mender")'
					to='/maintenance/mender/'
				>
					<header class='list-group-item-heading'>
						{{ $t('maintenance.mender.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('maintenance.mender.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("monit")'
					to='/maintenance/monit/'
				>
					<header class='list-group-item-heading'>
						{{ $t('maintenance.monit.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('maintenance.monit.description') }}
					</p>
				</CListGroupItem>
			</CListGroup>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

import {NavigationGuardNext, Route} from 'vue-router';

@Component({
	components: {
		CCard,
		CListGroup,
		CListGroupItem,
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('mender') &&
				!vm.$store.getters['features/isEnabled']('monit') &&
				!vm.$store.getters['features/isEnabled']('pixla')) {
				vm.$toast.error(vm.$t('maintenance.disabled').toString());
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'maintenance.title',
	},
})

/**
 * Maintenance disambiguation component
 */
export default class MaintenanceDisambiguation extends Vue {
}
</script>
