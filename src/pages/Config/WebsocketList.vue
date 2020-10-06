<template>
	<div>
		<h1>{{ $t('config.websocket.title') }}</h1>
		<div v-if='$store.getters["user/getRole"] === "power"'>
			<WebsocketMessagingList />
			<WebsocketServiceList />
		</div>
		<div v-else>
			<WebsocketInterfaceList />
		</div>
	</div>
</template>

<script lang='ts'>
import Vue from 'vue';
import WebsocketInterfaceList from '../../components/Config/WebsocketInterfaceList.vue';
import WebsocketMessagingList from '../../components/Config/WebsocketMessagingList.vue';
import WebsocketServiceList from '../../components/Config/WebsocketServiceList.vue';

export default Vue.extend({
	name: 'WebsocketList',
	components: {
		WebsocketInterfaceList,
		WebsocketMessagingList,
		WebsocketServiceList,
	},
	metaInfo: {
		title: 'config.websocket.title'
	}
});
</script>
