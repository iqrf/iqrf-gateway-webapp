

<template>
	<div>
		<CCard>
			<CCardHeader>
				<div>
					{{ $t('iqrfnet.standard.sensor-data.title') }}
				</div>
			</CCardHeader>
			<CCardBody>
				<div class='mb-2'>
					<b>
						{{ $t('iqrfnet.standard.sensor-data.status.title') }}: {{ statusMessage }}
					</b>
				</div>
				<div>
					<CButton
						class='mr-1'
						color='primary'
						size='sm'
						:disabled='actionRunning || !status || !status?.running || reading'
						@click='invoke()'
					>
						<CIcon :content='cilTouchApp' size='sm' />
						<span class='d-none d-lg-inline'>
							{{ $t('iqrfnet.standard.sensor-data.actions.invoke') }}
						</span>
					</CButton>
					<CButton
						class='mr-1'
						color='primary'
						size='sm'
						:disabled='actionRunning || !status || status?.running || reading'
						@click='start()'
					>
						<CIcon :content='cilMediaPlay' size='sm' />
						<span class='d-none d-lg-inline'>
							{{ $t('iqrfnet.standard.sensor-data.actions.start') }}
						</span>
					</CButton>
					<CButton
						class='mr-1'
						color='primary'
						size='sm'
						:disabled='actionRunning || !status || !status?.running || reading'
						@click='stop()'
					>
						<CIcon :content='cilMediaStop' size='sm' />
						<span class='d-none d-lg-inline'>
							{{ $t('iqrfnet.standard.sensor-data.actions.stop') }}
						</span>
					</CButton>
					<CButton
						class='mr-1'
						color='primary'
						size='sm'
						:disabled='actionRunning'
						@click='getStatus()'
					>
						<CIcon :content='cilReload' size='sm' />
						<span class='d-none d-lg-inline'>
							{{ $t('iqrfnet.standard.sensor-data.actions.status') }}
						</span>
					</CButton>
				</div>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import { Component, Vue } from 'vue-property-decorator';
import { CButton, CCard, CCardBody, CCardHeader } from '@coreui/vue/src';

import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import SensorDataService from '@/services/DaemonApi/SensorDataService';

import { MutationPayload } from 'vuex';
import { SensorDataMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SensorDataStatusResult } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { mapGetters } from 'vuex';
import { cilMediaPlay, cilMediaStop, cilReload, cilTouchApp } from '@coreui/icons';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
	},
	computed: {
		...mapGetters({
			reading: 'monitorClient/isDataReadRunning',
		}),
	},
	data: () => ({
		cilMediaPlay,
		cilMediaStop,
		cilReload,
		cilTouchApp,
	}),
})

/**
 * Sensor data controls component
 */
export default class SensorDataControls extends Vue {

	/**
	 * @var {string | null} msgId Message ID
	 */
	private msgId: string | null = null;

	/**
	 * Websocket mutation handler
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * @param {SensorDataStatusResult | null} status Worker status
	 */
	private status: SensorDataStatusResult | null = null;

	/**
	 * @var {boolean} actionRunning Indicates whether a request is being processed
	 */
	private actionRunning = false;

	/**
	 * Initializes mutation handling and validation rules
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			if (mutation.payload.mType === SensorDataMessages.Status) {
				this.handleStatus(mutation.payload.data);
			} else if (mutation.payload.mType === SensorDataMessages.Invoke) {
				this.handleInvoke(mutation.payload.data);
			} else if (mutation.payload.mType === SensorDataMessages.Start) {
				this.handleStart(mutation.payload.data);
			} else if (mutation.payload.mType === SensorDataMessages.Stop) {
				this.handleStop(mutation.payload.data);
			} else {
				this.actionRunning = false;
				this.$toast.error(
					this.$t('iqrfnet.messages.genericError').toString()
				);
			}
		});
	}

	/**
	 * Fetches worker status
	 */
	mounted(): void {
		this.getStatus();
	}

	/**
	 * Unregister mutation handling
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Computes status message
	 */
	get statusMessage() {
		if (this.status === null) {
			return this.$t('iqrfnet.standard.sensor-data.status.unknown');
		}
		if (!this.status.running) {
			return this.$t('iqrfnet.standard.sensor-data.status.inactive');
		}
		if (!this.$store.getters['monitorClient/isDataReadRunning']) {
			return this.$t('iqrfnet.standard.sensor-data.status.idle');
		}
		return this.$t('iqrfnet.standard.sensor-data.status.reading');
	}

	/**
	 * Invoke worker
	 */
	invoke(): void {
		const options = new DaemonMessageOptions(null, 5_000, 'iqrfnet.standard.sensor-data.messages.invoke.timeout', () => this.msgId = null);
		SensorDataService.invoke(options)
			.then((msgId: string) => {
				this.actionRunning = true;
				this.msgId = msgId;
			});
	}

	/**
	 * Handle invoke worker response
	 * @param response Response
	 */
	handleInvoke(response): void {
		this.actionRunning = false;
		if (response.status !== 0) {
			this.$toast.error(
				this.$t('iqrfnet.standard.sensor-data.messages.invoke.failed').toString(),
			);
			return;
		}
		this.$toast.success(
			this.$t('iqrfnet.standard.sensor-data.messages.invoke.success').toString(),
		);
		this.getStatus();
	}

	/**
	 * Start worker
	 */
	start(): void {
		const options = new DaemonMessageOptions(null, 5_000, 'iqrfnet.standard.sensor-data.messages.start.timeout', () => this.msgId = null);
		SensorDataService.start(options)
			.then((msgId: string) => {
				this.actionRunning = true;
				this.msgId = msgId;
			});
	}

	/**
	 * Handle start worker response
	 * @param response Response
	 */
	handleStart(response): void {
		this.actionRunning = false;
		if (response.status !== 0) {
			this.$toast.error(
				this.$t('iqrfnet.standard.sensor-data.messages.start.failed').toString(),
			);
			return;
		}
		this.$toast.success(
			this.$t('iqrfnet.standard.sensor-data.messages.start.success').toString(),
		);
		this.getStatus();
	}

	/**
	 * Stop worker
	 */
	stop(): void {
		const options = new DaemonMessageOptions(null, 5_000, 'iqrfnet.standard.sensor-data.messages.stop.timeout', () => this.msgId = null);
		SensorDataService.stop(options)
			.then((msgId: string) => {
				this.actionRunning = true;
				this.msgId = msgId;
			});
	}

	/**
	 * Handle stop worker response
	 * @param response Response
	 */
	handleStop(response): void {
		this.actionRunning = false;
		if (response.status !== 0) {
			this.$toast.error(
				this.$t('iqrfnet.standard.sensor-data.messages.stop.failed').toString(),
			);
			return;
		}
		this.$toast.success(
			this.$t('iqrfnet.standard.sensor-data.messages.stop.success').toString(),
		);
		this.getStatus();
	}

	/**
	 * Get worker status
	 */
	getStatus(): void {
		const options = new DaemonMessageOptions(null, 5_000, 'iqrfnet.standard.sensor-data.messages.status.timeout', () => this.msgId = null);
		SensorDataService.status(options)
			.then((msgId: string) => {
				this.actionRunning = true;
				this.msgId = msgId;
			});
	}

	/**
	 * Handle get worker status response
	 * @param response Response
	 */
	handleStatus(response): void {
		this.actionRunning = false;
		if (response.status !== 0) {
			this.$toast.error(
				this.$t('iqrfnet.standard.sensor-data.messages.status.failed').toString()
			);
			return;
		}
		this.status = response.rsp as SensorDataStatusResult;
	}

}
</script>
