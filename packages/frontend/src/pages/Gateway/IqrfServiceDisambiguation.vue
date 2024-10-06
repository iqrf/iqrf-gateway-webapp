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
		<h1>{{ $t('service.iqrf.title') }}</h1>
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
		title: 'service.iqrf.title',
	},
})

/**
 * Iqrf services menu component
 */
export default class IqrfServiceDisambiguation extends Vue {

	/**
	 * @var {Link[]} links Links for disambiguation menu
	 */
	private links: Array<Link> = [
		{
			title: this.$t('service.iqrf-gateway-daemon.title').toString(),
			description: this.$t('service.iqrf-gateway-daemon.description').toString(),
			to: '/gateway/service/iqrf-gateway-daemon/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('service.iqrf-gateway-controller.title').toString(),
			description: this.$t('service.iqrf-gateway-controller.description').toString(),
			to: '/gateway/service/iqrf-gateway-controller/',
			role: UserRoleIndex.NORMAL,
			feature: 'iqrfGatewayController',
		},
		{
			title: this.$t('service.iqrf-gateway-translator.title').toString(),
			description: this.$t('service.iqrf-gateway-translator.description').toString(),
			to: '/gateway/service/iqrf-gateway-translator/',
			role: UserRoleIndex.NORMAL,
			feature: 'iqrfGatewayTranslator',
		},
		{
			title: this.$t('service.iqrf-cloud-provisioning.title').toString(),
			description: this.$t('service.iqrf-cloud-provisioning.description').toString(),
			to: '/gateway/service/iqrf-cloud-provisioning/',
			role: UserRoleIndex.NORMAL,
			feature: 'iqrfCloudProvisioning',
		},
	];

}
</script>
