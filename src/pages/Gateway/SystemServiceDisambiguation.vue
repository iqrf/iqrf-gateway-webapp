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
		<h1>{{ $t('service.system.title') }}</h1>
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
		title: 'service.system.title',
	},
})

/**
 * System services menu component
 */
export default class IqrfServiceDisambiguation extends Vue {

	/**
	 * @var {Link[]} links Links for disambiguation menu
	 */
	private links: Array<Link> = [
		{
			title: this.$t('service.ssh.title').toString(),
			description: this.$t('service.ssh.description').toString(),
			to: '/gateway/service/ssh/',
			role: UserRoleIndex.ADMIN,
			feature: 'ssh',
		},
		{
			title: this.$t('service.nodered.title').toString(),
			description: this.$t('service.nodered.description').toString(),
			to: '/gateway/service/nodered/',
			role: UserRoleIndex.NORMAL,
			feature: 'nodeRed',
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
	];

}
</script>
