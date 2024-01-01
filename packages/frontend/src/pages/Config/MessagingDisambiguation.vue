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
		<h1>{{ $t('config.daemon.messagings.title') }}</h1>
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
		title: 'config.daemon.messagings.title'
	}
})

/**
 * Messagings menu disambiguation component
 */
export default class MessagingDisambiguation extends Vue {

	/**
	 * @var {Link[]} links Links for disambiguation menu
	 */
	private links: Array<Link> = [
		{
			title: this.$t('config.daemon.messagings.mqtt.title').toString(),
			description: this.$t('config.daemon.messagings.mqtt.description').toString(),
			to: '/config/daemon/messagings/mqtt',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('config.daemon.messagings.websocket.title').toString(),
			description: this.$t('config.daemon.messagings.websocket.description').toString(),
			to: '/config/daemon/messagings/websocket',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('config.daemon.messagings.mq.title').toString(),
			description: this.$t('config.daemon.messagings.mq.description').toString(),
			to: '/config/daemon/messagings/mq',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('config.daemon.messagings.udp.title').toString(),
			description: this.$t('config.daemon.messagings.udp.description').toString(),
			to: '/config/daemon/messagings/udp',
			role: UserRoleIndex.NORMAL,
		},
	];

}
</script>
