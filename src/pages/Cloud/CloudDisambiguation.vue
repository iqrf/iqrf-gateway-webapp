<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
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
			</CListGroup>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Options, Vue} from 'vue-property-decorator';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

@Options({
	components: {
		CCard,
		CListGroup,
		CListGroupItem
	},
	metaInfo: {
		title: 'cloud.title',
	},
})

/**
 * Cloud disambiguation menu component
 */
export default class CloudDisambiguation extends Vue {}
</script>
