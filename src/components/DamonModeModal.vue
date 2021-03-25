<template>
	<CModal
		:show.sync='daemonStatus.modal'
		color='warning'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('daemonStatus.modalTitle') }}
			</h5>
		</template>
		{{ $t('daemonStatus.modalPrompt') }}
		<template #footer>
			<CButton
				color='warning'
				@click='hideModal()'
			>
				{{ $t('forms.close') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';
import {mapGetters} from 'vuex';
import UrlBuilder from '../helpers/urlBuilder';

interface IMonitorMsgData {
	num: number
	timestamp: number
	dpaQueueLen: number
	iqrfChannelState: string
	dpaChannelState: string
	msgQueueLen: number
	operMode: string
}

interface IMonitorMsg {
	mType: string
	data: IMonitorMsgData
}

@Component({
	components: {
		CButton,
		CModal,
	},
	computed: {
		...mapGetters({
			daemonStatus: 'daemonStatus',
		}),
	},
})

/**
 * Daemon mode notice modal window
 */
export default class DaemonModeModal extends Vue {

	/**
	 * @var {number} reconnectInterval WebSocket reconnect interval ID
	 */
	private reconnectInterval = 0

	/**
	 * @var {WebSocket} webSocket WebSocket object
	 */
	private webSocket: WebSocket|null = null

	/**
	 * Initializes websocket
	 */
	created(): void {
		this.setSocket();
	}

	/**
	 * Creates websocket connection to daemon monitor server and sets callbacks
	 */
	private setSocket(): void {
		const urlBuilder: UrlBuilder = new UrlBuilder();
		this.webSocket = new WebSocket(urlBuilder.getWsMonitorUrl());
		this.webSocket.onmessage = (event) => {
			this.parseMonitor(JSON.parse(event.data));
		};
		this.webSocket.onerror = ()=> {
			this.webSocket?.close();
		};
		this.webSocket.onopen = () => {
			window.clearInterval(this.reconnectInterval);
			this.webSocket!.onclose = () => {
				this.reconnectInterval = window.setInterval(() => {
					this.setSocket();
				}, 5000);
			};
		};
	}

	/**
	 * Parses monitor message and applies changes to internal state
	 * @param {IMonitorMsg} message Monitor message object
	 */
	private parseMonitor(message: IMonitorMsg): void {
		let mode = message.data.operMode;
		if (mode !== this.$store.getters.daemonStatus.mode) {
			this.$store.dispatch('daemonStatusMode', mode);
		}
		this.$store.commit('UPDATE_DAEMON_QUEUE_LEN', message.data.msgQueueLen);
	}

	/**
	 * Requests to hide modal window
	 */
	private hideModal(): void {
		this.$store.dispatch('hideDaemonModal');
	}
}
</script>
