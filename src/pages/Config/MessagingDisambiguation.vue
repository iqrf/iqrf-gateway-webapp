<template>
	<div>
		<h1>{{ $t('config.daemon.messagings.title') }}</h1>
		<CCard body-wrapper>
			<CListGroup>
				<CListGroupItem
					to='/config/daemon/messagings/mqtt'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.daemon.messagings.mqtt.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.daemon.messagings.mqtt.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					to='/config/daemon/messagings/websocket'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.daemon.messagings.websocket.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.daemon.messagings.websocket.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					to='/config/daemon/messagings/mq'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.daemon.messagings.mq.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.daemon.messagings.mq.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					to='/config/daemon/messagings/udp'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.daemon.messagings.udp.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.daemon.messagings.udp.description') }}
					</p>
				</CListGroupItem>
			</CListGroup>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CSelect} from '@coreui/vue/src';
import MqttMessagingTable from '../../pages/Config/MqttMessagingTable.vue';
import WebsocketList from '../../pages/Config/WebsocketList.vue';
import MqMessagingTable from '../../pages/Config/MqMessagingTable.vue';
import UdpMessagingTable from '../../pages/Config/UdpMessagingTable.vue';

@Component({
	components: {
		CCard,
		CSelect,
		MqttMessagingTable,
		WebsocketList,
		MqMessagingTable,
		UdpMessagingTable,
	},
	metaInfo: {
		title: 'config.daemon.messagings.title'
	}
})

/**
 * Messagings menu disambiguation component
 */
export default class Messagings extends Vue {

}
</script>
