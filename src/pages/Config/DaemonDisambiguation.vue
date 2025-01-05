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
		<h1>{{ $t('config.daemon.title') }}</h1>
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
		title: 'config.daemon.title'
	}
})

/**
 * Daemon configuration disambiguation menu
 */
export default class DaemonDisambiguation extends Vue {

	/**
	 * @var {Link[]} links Links for disambiguation menu
	 */
	private links: Array<Link> = [
		{
			title: this.$t('config.daemon.main.title').toString(),
			description: this.$t('config.daemon.main.description').toString(),
			to: '/config/daemon/main/',
			role: UserRoleIndex.ADMIN,
		},
		{
			title: this.$t('config.daemon.interfaces.title').toString(),
			description: this.$t('config.daemon.interfaces.description').toString(),
			to: '/config/daemon/interfaces/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('config.daemon.messagings.title').toString(),
			description: this.$t('config.daemon.messagings.description').toString(),
			to: '/config/daemon/messagings/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('config.daemon.scheduler.title').toString(),
			description: this.$t('config.daemon.scheduler.description').toString(),
			to: '/config/daemon/scheduler/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('config.daemon.misc.title').toString(),
			description: this.$t('config.daemon.misc.description').toString(),
			to: '/config/daemon/misc/',
			role: UserRoleIndex.NORMAL,
		},
	];

}
</script>
