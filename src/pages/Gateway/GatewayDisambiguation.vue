<!--
Copyright 2017-2022 IQRF Tech s.r.o.
Copyright 2019-2022 MICRORISC s.r.o.

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
				<CListGroupItem
					v-if='roleIdx <= roles.basic'
					to='/gateway/info/'
				>
					<header class='list-group-item-heading'>
						{{ $t('gateway.info.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('gateway.info.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='roleIdx <= roles.normal'
					to='/gateway/date-time/'
				>
					<header class='list-group-item-heading'>
						{{ $t('gateway.datetime.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('gateway.datetime.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='roleIdx <= roles.normal'
					to='/gateway/log/'
				>
					<header class='list-group-item-heading'>
						{{ $t('gateway.log.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('gateway.log.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='roleIdx <= roles.normal'
					to='/gateway/change-mode/'
				>
					<header class='list-group-item-heading'>
						{{ $t('gateway.mode.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('gateway.mode.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='roleIdx <= roles.normal'
					to='/gateway/iqrf-services/'
				>
					<header class='list-group-item-heading'>
						{{ $t('service.iqrf.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('service.iqrf.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("ssh") && roleIdx <= roles.admin'
					to='/gateway/service/ssh/'
				>
					<header class='list-group-item-heading'>
						{{ $t('service.ssh.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('service.ssh.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("iTemp") && roleIdx <= roles.normal'
					to='/gateway/service/tempgw/'
				>
					<header class='list-group-item-heading'>
						{{ $t('service.tempgw.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('service.tempgw.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("unattendedUpgrades") && roleIdx <= roles.admin'
					to='/gateway/service/unattended-upgrades/'
				>
					<header class='list-group-item-heading'>
						{{ $t('service.unattended-upgrades.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('service.unattended-upgrades.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("systemdJournal") && roleIdx <= roles.admin'
					to='/gateway/service/systemd-journald/'
				>
					<header class='list-group-item-heading'>
						{{ $t('service.systemd-journald.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('service.systemd-journald.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("apcupsd") && roleIdx <= roles.normal'
					to='/gateway/service/apcupsd/'
				>
					<header class='list-group-item-heading'>
						{{ $t('service.apcupsd.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('service.apcupsd.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='roleIdx <= roles.normal'
					to='/gateway/power/'
				>
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
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

import {getRoleIndex} from '@/helpers/user';

@Component({
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
	/**
	 * @var {number} roleIdx Index of role in user role enum
	 */
	private roleIdx = 0;

	/**
	 * @constant {Record<string, number>} roles Dictionary of role indices
	 */
	private roles: Record<string, number> = {
		admin: 0,
		normal: 1,
		basicadmin: 2,
		basic: 3,
	};

	/**
	 * Retrieves user role and calculates the role index
	 */
	private created(): void {
		const roleVal = this.$store.getters['user/getRole'];
		this.roleIdx = getRoleIndex(roleVal);
	}
}
</script>
