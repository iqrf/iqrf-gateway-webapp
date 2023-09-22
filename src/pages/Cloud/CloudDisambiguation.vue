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
		<h1>{{ $t('cloud.title') }}</h1>
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
		title: 'cloud.title',
	},
})

/**
 * Cloud disambiguation menu component
 */
export default class CloudDisambiguation extends Vue {

	/**
	 * @var {Link[]} links Links for disambiguation menu
	 */
	private links: Array<Link> = [
		{
			title: this.$t('cloud.ibmCloud.title').toString(),
			description: this.$t('cloud.ibmCloud.description').toString(),
			to: '/cloud/ibm-cloud/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('cloud.msAzure.title').toString(),
			description: this.$t('cloud.msAzure.description').toString(),
			to: '/cloud/azure/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('cloud.amazonAws.title').toString(),
			description: this.$t('cloud.amazonAws.description').toString(),
			to: '/cloud/aws/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('cloud.intelimentsInteliGlue.title').toString(),
			description: this.$t('cloud.intelimentsInteliGlue.description').toString(),
			to: '/cloud/inteli-glue/',
			role: UserRoleIndex.NORMAL,
		},
	];

}
</script>
