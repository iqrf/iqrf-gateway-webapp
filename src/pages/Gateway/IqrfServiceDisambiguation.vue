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
import {Options, Vue} from 'vue-property-decorator';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

@Options({
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
