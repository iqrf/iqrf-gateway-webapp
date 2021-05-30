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
		<h1>{{ $t('core.dashboard') }}</h1>
		<CCard>
			<CCardBody>
				<CListGroup>
					<CListGroupItem to='/gateway/'>
						<header class='list-group-item-heading'>
							{{ $t('gateway.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('gateway.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem to='/config/'>
						<header class='list-group-item-heading'>
							{{ $t('config.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem to='/iqrfnet/'>
						<header class='list-group-item-heading'>
							{{ $t('iqrfnet.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('iqrfnet.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem v-if='$store.getters["features/isEnabled"]("networkManager")' to='/network/'>
						<header class='list-group-item-heading'>
							{{ $t('network.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('network.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem to='/cloud/'>
						<header class='list-group-item-heading'>
							{{ $t('cloud.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('cloud.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem to='/maintenance/'>
						<header class='list-group-item-heading'>
							{{ $t('maintenance.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('maintenance.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='$store.getters["features/isEnabled"]("grafana")'
						:href='$store.getters["features/configuration"]("grafana").url'
						target='_blank'
					>
						<header class='list-group-item-heading'>
							{{ $t('core.grafana.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('core.grafana.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='$store.getters["features/isEnabled"]("nodeRed")'
						:href='$store.getters["features/configuration"]("nodeRed").url'
						target='_blank'
					>
						<header class='list-group-item-heading'>
							{{ $t('core.nodeRed.workflow.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('core.nodeRed.workflow.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='$store.getters["features/isEnabled"]("nodeRed")'
						:href='$store.getters["features/configuration"]("nodeRed").url + "ui/"'
						target='_blank'
					>
						<header class='list-group-item-heading'>
							{{ $t('core.nodeRed.dashboard.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('core.nodeRed.dashboard.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='$store.getters["features/isEnabled"]("supervisord")'
						:href='$store.getters["features/configuration"]("supervisord").url'
						target='_blank'
					>
						<header class='list-group-item-heading'>
							{{ $t('core.supervisor.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('core.supervisor.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem to='/user/'>
						<header class='list-group-item-heading'>
							{{ $t('core.user.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('core.user.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem to='/api-key/'>
						<header class='list-group-item-heading'>
							{{ $t('core.apiKey.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('core.apiKey.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='$store.getters["features/isEnabled"]("docs")'
						:href='$store.getters["features/configuration"]("docs").url'
						target='_blank'
					>
						<header class='list-group-item-heading'>
							{{ $t('core.documentation.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('core.documentation.description') }}
						</p>
					</CListGroupItem>
				</CListGroup>
			</CCardBody>
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
		title: 'core.dashboard',
	},
})

/**
 * Main disambiguation menu component
 */
export default class MainDisambiguation extends Vue {
}
</script>
