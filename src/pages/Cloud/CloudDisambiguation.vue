<template>
	<div>
		<h1>{{ $t('cloud.title') }}</h1>
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
		title: 'cloud.title',
	},
})

export default class CloudDisambiguation extends Vue {}
</script>
