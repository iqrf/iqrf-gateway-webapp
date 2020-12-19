<template>
	<div>
		<h1>{{ $t('gateway.mode.title') }}</h1>
		<CCard body-wrapper>
			<CRow style='margin-bottom: 1.25rem'>
				<CCol>
					{{ $t('gateway.info.gwMode') }}
				</CCol>
				<CCol>
					{{ $t(mode !== 'unknown' ? 'gateway.mode.modes.' + mode: 'gateway.mode.messages.getFailed') }}
				</CCol>
			</CRow>
			<div v-if='mode !== "unknown"'>
				<CButton
					color='primary'
					@click='setMode(modes.operational)'
				>
					{{ $t('gateway.mode.modes.operational') }}
				</CButton> <CButton
					color='primary'
					@click='setMode(modes.service)'
				>
					{{ $t('gateway.mode.modes.service') }}
				</CButton> <CButton
					color='primary'
					@click='setMode(modes.forwarding)'
				>
					{{ $t('gateway.mode.modes.forwarding') }}
				</CButton>
			</div>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {MutationPayload} from 'vuex';
import {CButton, CCard} from '@coreui/vue/src';
import DaemonModeService, {DaemonModeEnum} from '../../services/DaemonModeService';
import { WebSocketClientState } from '../../store/modules/webSocketClient.module';
import { Dictionary } from 'vue-router/types/router';

@Component({
	components: {
		CButton,
		CCard,
	},
	metaInfo: {
		title: 'gateway.mode.title',
	}
})

/**
 * IQRF Gateway Daemon mode viewer component
 */
export default class DaemonMode extends Vue {
	/**
	 * @constant {Array<string>} allowedMTypes Array of allowed daemon api messages
	 */
	private allowedMTypes: Array<string> = [
		'mngDaemon_Mode',
		'messageError'
	]

	/**
	 * @var {boolean} loaded Auxiliary property to help choose correct message
	 */
	private loaded = false

	/**
	 * @var {DaemonModeEnum} mode Current Daemon mode
	 */
	private mode: DaemonModeEnum = DaemonModeEnum.unknown

	/**
	 * @constant {DaemonModeEnum} modes Daemon mode options
	 */
	private modes: Dictionary<DaemonModeEnum> = {
		forwarding: DaemonModeEnum.forwarding,
		operational: DaemonModeEnum.operational,
		service: DaemonModeEnum.service
	}

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	/**
	 * Component unwatch function
	 */
	private unwatch: CallableFunction = () => {return;}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				if (mutation.payload.data.msgId === this.msgId) {
					this.$store.dispatch('removeMessage', this.msgId);
					this.handleResponse(mutation.payload);
				}
			}
		});
		if (this.$store.getters.isSocketConnected) {
			this.getMode();
		} else {
			this.unwatch = this.$store.watch(
				(state: WebSocketClientState, getter: any) => getter.isSocketConnected,
				(newVal: boolean, oldVal: boolean) => {
					if (!oldVal && newVal) {
						this.getMode();
						this.unwatch();
					}
				}
			);
		}
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('removeMessage', this.msgId);
		this.unwatch();
		this.unsubscribe();
	}

	/**
	 * Daemon api response handler
	 */
	private handleResponse(response: any): void {
		this.mode = DaemonModeService.parse(response);
		this.$store.commit('spinner/HIDE');
		if (this.mode === DaemonModeEnum.unknown) {
			this.$toast.error(
				this.$t('gateway.mode.messages.' + (this.loaded ? 'set' : 'get') + 'Failed')
					.toString()
			);
		} else if (this.loaded) {
			this.$toast.success(
				this.$t('gateway.mode.messages.' + this.mode).toString()
			);
		} else {
			this.loaded = true;
		}
	}

	/**
	 * Retrieves Daemon mode
	 */
	private getMode(): void {
		DaemonModeService.get(5000, 'gateway.mode.messages.getFailed', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Sets new Daemon mode
	 * @param {DaemonModeEnum} newMode New Daemon mode to set
	 */
	private setMode(newMode: DaemonModeEnum): void {
		this.$store.dispatch('spinner/hide');
		DaemonModeService.set(newMode as DaemonModeEnum, 5000, 'gateway.mode.messages.setFailed', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}
}
</script>
