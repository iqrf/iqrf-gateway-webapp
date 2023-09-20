<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
		<Disambiguation :links='links' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import Disambiguation from '@/components/Disambiguation.vue';
import {Link} from '@/helpers/DisambiguationHelper';
import {UserRoleIndex} from '@/services/AuthenticationService';

@Component({
	components: {
		Disambiguation,
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
	 * @var {Link[]} links Links for disambiguation menu
	 */
	private links: Array<Link> = [
		{
			title: this.$t('gateway.info.title').toString(),
			description: this.$t('gateway.info.description').toString(),
			to: '/gateway/info/',
			role: UserRoleIndex.BASIC,
		},
		{
			title: this.$t('gateway.datetime.title').toString(),
			description: this.$t('gateway.datetime.description').toString(),
			to: '/gateway/date-time/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('gateway.log.title').toString(),
			description: this.$t('gateway.log.description').toString(),
			to: '/gateway/log/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('gateway.mode.title').toString(),
			description: this.$t('gateway.mode.description').toString(),
			to: '/gateway/change-mode/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('service.iqrf.title').toString(),
			description: this.$t('service.iqrf.description').toString(),
			to: '/gateway/iqrf-services/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('service.ssh.title').toString(),
			description: this.$t('service.ssh.description').toString(),
			to: '/gateway/service/ssh/',
			role: UserRoleIndex.ADMIN,
			feature: 'ssh',
		},
		{
			title: this.$t('service.tempgw.title').toString(),
			description: this.$t('service.tempgw.description').toString(),
			to: '/gateway/service/tempgw/',
			role: UserRoleIndex.NORMAL,
			feature: 'iTemp',
		},
		{
			title: this.$t('service.unattended-upgrades.title').toString(),
			description: this.$t('service.unattended-upgrades.description').toString(),
			to: '/gateway/service/unattended-upgrades/',
			role: UserRoleIndex.ADMIN,
			feature: 'unattendedUpgrades',
		},
		{
			title: this.$t('service.systemd-journald.title').toString(),
			description: this.$t('service.systemd-journald.description').toString(),
			to: '/gateway/service/systemd-journald/',
			role: UserRoleIndex.ADMIN,
			feature: 'journal',
		},
		{
			title: this.$t('service.apcupsd.title').toString(),
			description: this.$t('service.apcupsd.description').toString(),
			to: '/gateway/service/apcupsd/',
			role: UserRoleIndex.NORMAL,
			feature: 'apcupsd',
		},
		{
			title: this.$t('gateway.power.title').toString(),
			description: this.$t('gateway.power.description').toString(),
			to: '/gateway/power/',
			role: UserRoleIndex.NORMAL,
		},
	];

}
</script>
