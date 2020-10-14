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
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

@Component({
	components: {
		CCard,
		CListGroup,
		CListGroupItem
	},
	metaInfo: {
		title: 'network.title',
	},
})

export default class NetworkDisambiguation extends Vue {
}
</script>
