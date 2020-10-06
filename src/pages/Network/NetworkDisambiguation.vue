<template>
	<div>
		<h1>{{ $t('network.title') }}</h1>
		<CCard body-wrapper>
			<CListGroup>
				<CListGroupItem to='/network/ethernet'>
					<header class='list-group-item-heading'>
						{{ $t('network.ethernet.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('network.ethernet.description') }}
					</p>
				</CListGroupItem>
			</CListGroup>
		</CCard>
	</div>
</template>

<script lang='ts'>
import Vue from 'vue';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

export default Vue.extend({
	name: 'NetworkDisambiguation',
	components: {
		CCard,
		CListGroup,
		CListGroupItem
	},
	metaInfo: {
		title: 'network.title',
	},
});
</script>
