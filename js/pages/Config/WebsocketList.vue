<template>
	<div v-if='$store.getters["user/getRole"] === "power"'>
		<WebsocketMessagingList />
		<WebsocketServiceList />
	</div>
	<div v-else>
		<WebsocketInterfaceList />
	</div>
</template>

<script>
import WebsocketInterfaceList from '../../components/Config/WebsocketInterfaceList';
import WebsocketMessagingList from '../../components/Config/WebsocketMessagingList';
import WebsocketServiceList from '../../components/Config/WebsocketServiceList';

export default {
	name: 'WebsocketList',
	components: {
		WebsocketInterfaceList,
		WebsocketMessagingList,
		WebsocketServiceList,
	},
	metaInfo: {
		title: 'config.websocket.title'
	}
};
</script>
