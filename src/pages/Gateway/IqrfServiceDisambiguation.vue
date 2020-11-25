<template>
	<div>
		<h1>{{ $t('service.iqrf.title') }}</h1>
		<CCard body-wrapper>
			<CListGroup>
				<CListGroupItem to='/gateway/service/iqrf-gateway-daemon/'>
					<header class='list-group-item-heading'>
						{{ $t('service.iqrf-gateway-daemon.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('service.iqrf-gateway-daemon.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/gateway/service/iqrf-gateway-controller/'>
					<header class='list-group-item-heading'>
						{{ $t('service.iqrf-gateway-controller.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('service.iqrf-gateway-controller.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/gateway/service/iqrf-gateway-translator/'>
					<header class='list-group-item-heading'>
						{{ $t('service.iqrf-gateway-translator.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('service.iqrf-gateway-translator.description') }}
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
		title: 'service.iqrf.title',
	},
})

/**
 * Iqrf services menu component
 */
export default class IqrfServiceDisambiguation extends Vue {
}
</script>
