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
		<h1>{{ $t('network.title') }}</h1>
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
		title: 'network.title',
	},
})

/**
 * Network disambiguation menu component
 */
export default class NetworkDisambiguation extends Vue {

	/**
	 * @var {Link[]} links Links for disambiguation menu
	 */
	private links: Array<Link> = [
		{
			title: this.$t('network.ethernet.title').toString(),
			description: this.$t('network.ethernet.description').toString(),
			to: '/ip-network/ethernet',
			role: UserRoleIndex.ADMIN,
		},
		{
			title: this.$t('network.wireless.title').toString(),
			description: this.$t('network.wireless.description').toString(),
			to: '/ip-network/wireless',
			role: UserRoleIndex.ADMIN,
		},
		{
			title: this.$t('network.mobile.title').toString(),
			description: this.$t('network.mobile.description').toString(),
			to: '/ip-network/mobile',
			role: UserRoleIndex.ADMIN,

		}, {
			title: this.$t('network.wireguard.title').toString(),
			description: this.$t('network.wireguard.description').toString(),
			to: '/ip-network/vpn',
			role: UserRoleIndex.ADMIN,
		},
	];

}
</script>
