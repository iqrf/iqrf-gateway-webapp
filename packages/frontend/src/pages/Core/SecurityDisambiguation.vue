<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		<h1>{{ $t('core.security.title') }}</h1>
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
		title: 'core.security.title',
	},
})

/**
 * Security disambiguation menu component
 */
export default class SecurityDisambiguation extends Vue {

	/**
	 * @var {Link[]} links Links for disambiguation menu
	 */
	private links: Array<Link> = [
		{
			title: this.$t('core.security.apiKey.title').toString(),
			description: this.$t('core.security.apiKey.description').toString(),
			to: '/security/api-key/',
			role: UserRoleIndex.ADMIN,
		},
		{
			title: this.$t('core.security.ssh.title').toString(),
			description: this.$t('core.security.ssh.description').toString(),
			to: '/security/ssh-key/',
			role: UserRoleIndex.ADMIN,
			feature: 'ssh',
		},
	];

}
</script>
