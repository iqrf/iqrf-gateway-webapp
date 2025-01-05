<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
		<h1>{{ $t('maintenance.title') }}</h1>
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
		title: 'maintenance.title',
	},
})

/**
 * Maintenance disambiguation component
 */
export default class MaintenanceDisambiguation extends Vue {

	/**
	 * @var {Link[]} links Links for disambiguation menu
	 */
	private links: Array<Link> = [
		{
			title: this.$t('maintenance.backup.title').toString(),
			description: this.$t('maintenance.backup.description').toString(),
			to: '/maintenance/backup-restore/',
			role: UserRoleIndex.ADMIN,
		},
		{
			title: this.$t('maintenance.mender.title').toString(),
			description: this.$t('maintenance.mender.description').toString(),
			to: '/maintenance/mender/',
			role: UserRoleIndex.ADMIN,
			feature: 'mender',
		},
		{
			title: this.$t('maintenance.monit.title').toString(),
			description: this.$t('maintenance.monit.description').toString(),
			to: '/maintenance/monit/',
			role: UserRoleIndex.ADMIN,
			feature: 'monit',
		},
	];

}
</script>
