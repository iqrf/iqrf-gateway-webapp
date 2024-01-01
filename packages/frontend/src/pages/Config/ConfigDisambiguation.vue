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
		<h1>{{ $t('config.title') }}</h1>
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
		title: 'config.title',
	},
})

/**
 * Config disambiguation menu component
 */
export default class ConfigDisambiguation extends Vue {

	/**
	 * @var {Link[]} links Links for disambiguation menu
	 */
	private links: Array<Link> = [
		{
			title: this.$t('config.daemon.title').toString(),
			description: this.$t('config.daemon.description').toString(),
			to: '/config/daemon/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('config.controller.title').toString(),
			description: this.$t('config.controller.description').toString(),
			to: '/config/controller/',
			role: UserRoleIndex.NORMAL,
			feature: 'iqrfGatewayController',
		},
		{
			title: this.$t('config.translator.title').toString(),
			description: this.$t('config.translator.description').toString(),
			to: '/config/translator/',
			role: UserRoleIndex.NORMAL,
			feature: 'iqrfGatewayTranslator',
		},
		{
			title: this.$t('config.repository.title').toString(),
			description: this.$t('config.repository.description').toString(),
			to: '/config/repository/',
			role: UserRoleIndex.NORMAL,
			feature: 'iqrfRepository',
		},
		{
			title: this.$t('config.smtp.title').toString(),
			description: this.$t('config.smtp.description').toString(),
			to: '/config/smtp/',
			role: UserRoleIndex.ADMIN,
		},
	];

}
</script>
