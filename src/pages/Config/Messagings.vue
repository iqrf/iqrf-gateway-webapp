<template>
	<CCard>
		<CCardHeader>
			<h3>{{ $t('config.daemon.messagings.title') }}</h3>
		</CCardHeader>
		<CCardBody>
			<CSelect
				:value.sync='messaging'
				:options='messagingOptions'
				:label='$t("config.daemon.form.messaging")'
			/>
			<MqttMessagingTable v-if='messaging === "mqtt"' />
			<WebsocketList v-if='messaging === "ws"' />
			<MqMessagingTable v-if='messaging === "mq"' />
			<UdpMessagingTable v-if='messaging === "udp"' />
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader, CSelect} from '@coreui/vue/src';
import MqttMessagingTable from '../../pages/Config/MqttMessagingTable.vue';
import WebsocketList from '../../pages/Config/WebsocketList.vue';
import MqMessagingTable from '../../pages/Config/MqMessagingTable.vue';
import UdpMessagingTable from '../../pages/Config/UdpMessagingTable.vue';
import {IOption} from '../../interfaces/coreui';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
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
 * Messagings configuration page component
 */
export default class Messagings extends Vue {
	/**
	 * @var {string} messaging Currently selected messaging
	 */
	private messaging = 'mqtt'
	
	/**
	 * @constant {Array<IOption>} messagingOptions Array of CoreUI select options for messagings
	 */
	private messagingOptions: Array<IOption> = [
		{
			value: 'mqtt',
			label: this.$t('config.mqtt.title').toString()
		},
		{
			value: 'ws',
			label: this.$t('config.websocket.title').toString()
		},
		{
			value: 'mq',
			label: this.$t('config.mq.title').toString()
		},
		{
			value: 'udp',
			label: this.$t('config.udp.title').toString()
		}
	]
}
</script>
