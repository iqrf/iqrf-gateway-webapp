<template>
	<CCard body-wrapper>
		<CListGroup>
			<CListGroupItem to='/iqrfnet/send-raw/'>
				<header class='list-group-item-heading'>
					{{ $t('iqrfnet.sendPacket.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('iqrfnet.sendPacket.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/iqrfnet/send-json/'>
				<header class='list-group-item-heading'>
					{{ $t('iqrfnet.sendJson.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('iqrfnet.sendJson.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem
				v-if='$store.getters["features/isEnabled"]("trUpload")'
				to='/iqrfnet/tr-upload/'
			>
				<header class='list-group-item-heading'>
					{{ $t('iqrfnet.trUpload.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('iqrfnet.trUpload.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/iqrfnet/tr-config/'>
				<header class='list-group-item-heading'>
					{{ $t('iqrfnet.trConfiguration.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('iqrfnet.trConfiguration.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/iqrfnet/network/'>
				<header class='list-group-item-heading'>
					{{ $t('iqrfnet.networkManager.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('iqrfnet.networkManager.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/iqrfnet/standard/'>
				<header class='list-group-item-heading'>
					{{ $t('iqrfnet.standard.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('iqrfnet.standard.description') }}
				</p>
			</CListGroupItem>
		</CListGroup>
	</CCard>
</template>

<script>
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

export default {
	name: 'IqrfNetDisambiguation',
	components: {
		CCard,
		CListGroup,
		CListGroupItem
	},
	metaInfo: {
		title: 'iqrfnet.title',
	},
};
</script>
