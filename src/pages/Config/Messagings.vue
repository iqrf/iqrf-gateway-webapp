<template>
	<div>
		<h1>{{ $t('config.daemon.messagings.title') }}</h1>
		<CCard body-wrapper>
			<CSelect
				:value.sync='activeMessaging'
				:options='messagingOptions'
				:label='$t("config.daemon.form.messaging")'
			/>
		</CCard>
		<MqttMessagingTable v-if='activeMessaging === "mqtt"' />
		<WebsocketList v-if='activeMessaging === "ws"' />
		<MqMessagingTable v-if='activeMessaging === "mq"' />
		<UdpMessagingTable v-if='activeMessaging === "udp"' />
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue, Watch} from 'vue-property-decorator';
import {CCard, CSelect} from '@coreui/vue/src';
import MqttMessagingTable from '../../pages/Config/MqttMessagingTable.vue';
import WebsocketList from '../../pages/Config/WebsocketList.vue';
import MqMessagingTable from '../../pages/Config/MqMessagingTable.vue';
import UdpMessagingTable from '../../pages/Config/UdpMessagingTable.vue';
import {IOption} from '../../interfaces/coreui';

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
 * Messagings configuration page component
 */
export default class Messagings extends Vue {
	
	/**
	 * @var {string} activeMessaging Currently selected messaging to display table of messagings
	 */
	private activeMessaging = '';
	
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

	/**
	 * Watcher for active messaging select to update URL
	 */
	@Watch('activeMessaging')
	private updateRouter(): void {
		this.$router.replace('/config/daemon/messagings/' + this.activeMessaging);
	}

	/**
	 * @property {string} messaging Messaging type passed to component via router
	 */
	@Prop({required: false, default: 'mqtt'}) messaging!: string

	/**
	 * Vue lifecycle component mounted
	 */
	mounted(): void {
		this.activeMessaging = this.messaging;
	}
}
</script>
