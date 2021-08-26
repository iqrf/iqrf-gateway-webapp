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
		<h1>{{ $t('config.title') }}</h1>
		<CCard>
			<CCardBody>
				<CListGroup>
					<CListGroupItem to='/config/daemon/'>
						<header class='list-group-item-heading'>
							{{ $t('config.daemon.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.daemon.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='$store.getters["features/isEnabled"]("iqrfGatewayController")'
						to='/config/controller/'
					>
						<header class='list-group-item-heading'>
							{{ $t('config.controller.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.controller.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='$store.getters["features/isEnabled"]("iqrfGatewayTranslator")'
						to='/config/translator/'
					>
						<header class='list-group-item-heading'>
							{{ $t('config.translator.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.translator.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem to='/config/smtp/'>
						<header class='list-group-item-heading'>
							{{ $t('config.smtp.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.smtp.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem to='/config/migration/'>
						<header class='list-group-item-heading'>
							{{ $t('config.migration.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.migration.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem to='/config/repository/'>
						<header class='list-group-item-heading'>
							{{ $t('config.repository.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.repository.description') }}
						</p>
					</CListGroupItem>
				</CListGroup>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader, CListGroup, CListGroupItem} from '@coreui/vue/src';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
		CListGroup,
		CListGroupItem
	},
	metaInfo: {
		title: 'config.title',
	},
})

/**
 * Config disambiguation menu component
 */
export default class ConfigDisambiguation extends Vue {
}
</script>
