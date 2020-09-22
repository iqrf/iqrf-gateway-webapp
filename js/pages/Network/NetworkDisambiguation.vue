<template>
	<CCard body-wrapper>
		<CListGroup>
			<CListGroupItem to='/network/ethernet/'>
				<header class='list-group-item-heading'>
					{{ $t('network.ethernet.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('network.ethernet.description') }}
				</p>
			</CListGroupItem>
		</CListGroup>
	</CCard>
</template>

<script>
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

export default {
	name: 'NetworkDisambiguation',
	components: {
		CCard,
		CListGroup,
		CListGroupItem
	},
	metaInfo: {
		title: 'network.title',
	},
};
</script>
