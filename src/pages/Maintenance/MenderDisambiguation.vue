<template>
	<div>
		<h1>{{ $t('maintenance.mender.title') }}</h1>
		<CCard body-wrapper>
			<CListGroup>
				<CListGroupItem
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
import {Options, Vue} from 'vue-property-decorator';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

import {NavigationGuardNext} from 'vue-router';

@Options({
	components: {
		CCard,
		CListGroup,
		CListGroupItem,
	},
	beforeRouteEnter(to, from, next: NavigationGuardNext): void {
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
}
</script>
