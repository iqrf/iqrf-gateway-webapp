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
		<h1>{{ $t('gateway.title') }}</h1>
		<CCard body-wrapper>
			<CListGroup>
				<CListGroupItem to='/gateway/info/'>
					<header class='list-group-item-heading'>
						{{ $t('gateway.info.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('gateway.info.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/gateway/date-time/'>
					<header class='list-group-item-heading'>
						{{ $t('gateway.datetime.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('gateway.datetime.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/gateway/log/'>
					<header class='list-group-item-heading'>
						{{ $t('gateway.log.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('gateway.log.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/gateway/change-mode/'>
					<header class='list-group-item-heading'>
						{{ $t('gateway.mode.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('gateway.mode.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/gateway/iqrf-services/'>
					<header class='list-group-item-heading'>
						{{ $t('service.iqrf.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('service.iqrf.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("ssh")'
					to='/service/ssh/'
				>
					<header class='list-group-item-heading'>
						{{ $t('service.ssh.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('service.ssh.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("unattendedUpgrades")'
					to='/service/unattended-upgrades/'
				>
					<header class='list-group-item-heading'>
						{{ $t('service.unattended-upgrades.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('service.unattended-upgrades.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("updater")'
					to='/gateway/updater/'
				>
					<header class='list-group-item-heading'>
						{{ $t('gateway.updater.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('gateway.updater.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/gateway/power/'>
					<header class='list-group-item-heading'>
						{{ $t('gateway.power.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('gateway.power.description') }}
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
		title: 'gateway.title',
	},
})

/**
 * Gateway disambiguation menu component
 */
export default class GatewayDisambiguation extends Vue {
}
</script>
