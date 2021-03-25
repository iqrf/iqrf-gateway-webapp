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

@Component({
	components: {
		CCard,
		CListGroup,
		CListGroupItem,
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
