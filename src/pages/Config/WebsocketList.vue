<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
		<div v-if='isAdmin'>
			<WebsocketMessagingList />
			<WebsocketServiceList />
		</div>
		<div v-else>
			<WebsocketInterfaceList />
		</div>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import WebsocketInterfaceList from '@/components/Config/Messagings/WebsocketInterfaceList.vue';
import WebsocketMessagingList from '@/components/Config/Messagings/WebsocketMessagingList.vue';
import WebsocketServiceList from '@/components/Config/Messagings/WebsocketServiceList.vue';

import {UserRole} from '@/services/AuthenticationService';

@Component({
	components: {
		WebsocketInterfaceList,
		WebsocketMessagingList,
		WebsocketServiceList,
	},
	metaInfo: {
		title: 'config.daemon.messagings.websocket.title'
	}
})

/**
 * Daemon WebSocket messaging page component
 */
export default class WebsocketList extends Vue {

	/**
	 * Checks if user is an administrator
	 * @returns {boolean} True if user is an administrator
	 */
	get isAdmin(): boolean {
		return this.$store.getters['user/getRole'] === UserRole.ADMIN;
	}

}
</script>
