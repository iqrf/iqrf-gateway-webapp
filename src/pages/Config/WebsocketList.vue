<template>
	<div>
		<h1>{{ $t('config.websocket.title') }}</h1>
		<div v-if='powerUser'>
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
import WebsocketInterfaceList from '../../components/Config/WebsocketInterfaceList.vue';
import WebsocketMessagingList from '../../components/Config/WebsocketMessagingList.vue';
import WebsocketServiceList from '../../components/Config/WebsocketServiceList.vue';

@Component({
	components: {
		WebsocketInterfaceList,
		WebsocketMessagingList,
		WebsocketServiceList,
	},
	metaInfo: {
		title: 'config.websocket.title'
	}
})

/**
 * Daemon WebSocket messaging page component
 */
export default class WebsocketList extends Vue {
	/**
	 * @var {boolean} powerUser Indicates whether the user is a power user
	 */
	private powerUser = false

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		if (this.$store.getters['user/getRole'] === 'power') {
			this.powerUser = true;
		}
	}
}
</script>
