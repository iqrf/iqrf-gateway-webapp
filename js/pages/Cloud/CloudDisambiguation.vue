<template>
	<CCard body-wrapper>
		<CListGroup>
			<CListGroupItem to='/cloud/ibm-cloud/'>
				<header class='list-group-item-heading'>
					{{ $t('cloud.ibmCloud.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('cloud.ibmCloud.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/cloud/azure/'>
				<header class='list-group-item-heading'>
					{{ $t('cloud.msAzure.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('cloud.msAzure.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/cloud/aws/'>
				<header class='list-group-item-heading'>
					{{ $t('cloud.amazonAws.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('cloud.amazonAws.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/cloud/hexio/'>
				<header class='list-group-item-heading'>
					{{ $t('cloud.hexio.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('cloud.hexio.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/cloud/inteli-glue/'>
				<header class='list-group-item-heading'>
					{{ $t('cloud.intelimentsInteliGlue.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('cloud.intelimentsInteliGlue.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem
				v-if='$store.getters["features/isEnabled"]("pixla")'
				to='/cloud/pixla/'
			>
				<header class='list-group-item-heading'>
					{{ $t('cloud.pixla.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('cloud.pixla.description') }}
				</p>
			</CListGroupItem>
		</CListGroup>
	</CCard>
</template>

<script lang='ts'>
import Vue from 'vue';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

export default Vue.extend({
	name: 'CloudDisambiguation',
	components: {
		CCard,
		CListGroup,
		CListGroupItem
	},
	metaInfo: {
		title: 'cloud.title',
	},
});
</script>
