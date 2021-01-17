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
		this.webSocket = new WebSocket(
			(window.location.protocol === 'https' ? 'wss://' : 'ws://')
			+ window.location.hostname
			+ (window.location.port === '8081' ? ':1438' : ':' + window.location.port + '/wsMonitor'));
		this.webSocket.onmessage = (event) => {
			this.parseMonitor(JSON.parse(event.data));
		};
		this.webSocket.onerror = ()=> {
			this.webSocket!.close();
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
		let daemonReady = this.$store.getters.daemonStatus.ready;
		if (daemonReady && mode === 'service') {
			this.$store.dispatch('daemonStatusNotReady');
		} else if (!daemonReady && (mode === 'operational' || mode === 'forwarding')) {
			this.$store.dispatch('daemonStatusReady');
		}
		this.$store.dispatch('daemonStatusMode', mode);
	}

	/**
	 * Requests to hide modal window
	 */
	private hideModal(): void {
		this.$store.dispatch('hideDaemonModal');
	}
}
</script>
