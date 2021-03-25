<template>
	<div>
		<h1>{{ $t('gateway.mode.title') }}</h1>
		<CCard body-wrapper>
			<table class='table table-striped'>
				<tbody>
					<tr>
						<th>{{ $t('gateway.mode.currentMode') }}</th>
						<td style='text-align: right;'>
							<div 
								v-if='!loaded && mode === "unknown"'
							>
								{{ $t('gateway.mode.modes.unknown') }}
							</div>
							<div 
								v-else-if='loaded && mode === "unknown"'
							>
								{{ $t('gateway.mode.messages.getFailed') }}
							</div>
							<CDropdown
								v-else
								color='primary'
								:toggler-text='$t("gateway.mode.modes." + mode)'
								size='sm'
							>
								<CDropdownItem @click='setMode(modes.operational)'>
									{{ $t('gateway.mode.modes.operational') }}
								</CDropdownItem>
								<CDropdownItem @click='setMode(modes.service)'>
									{{ $t('gateway.mode.modes.service') }}
								</CDropdownItem>
								<CDropdownItem @click='setMode(modes.forwarding)'>
									{{ $t('gateway.mode.modes.forwarding') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</tr>
					<tr>
						<th>{{ $t('gateway.mode.startupMode') }}</th>
						<td style='text-align: right;'>
							<CDropdown
								v-if='ideConfiguration !== null'
								color='primary'
								:toggler-text='$t("gateway.mode.modes." + ideConfiguration.operMode)'
								size='sm'
							>
								<CDropdownItem @click='setStartupMode(modes.operational)'>
									{{ $t('gateway.mode.modes.operational') }}
								</CDropdownItem>
								<CDropdownItem @click='setStartupMode(modes.service)'>
									{{ $t('gateway.mode.modes.service') }}
								</CDropdownItem>
								<CDropdownItem @click='setStartupMode(modes.forwarding)'>
									{{ $t('gateway.mode.modes.forwarding') }}
								</CDropdownItem>
							</CDropdown>
							<div
								v-else
							>
								{{ $t('gateway.mode.modes.unknown') }}
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CDropdown, CDropdownItem} from '@coreui/vue/src';

import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import DaemonModeService, {DaemonModeEnum} from '../../services/DaemonModeService';

import {AxiosError, AxiosResponse} from 'axios';
import {Dictionary} from 'vue-router/types/router';
import {MutationPayload} from 'vuex';
import {WebSocketClientState} from '../../store/modules/webSocketClient.module';
import {IIdeCounterpart} from '../../interfaces/ideCounterpart';

@Component({
	components: {
		CButton,
		CCard,
		CDropdown,
		CDropdownItem
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
	 * @constant {string} component IDE counterpart component name
	 */
	private ideComponent = 'iqrf::IdeCounterpart'

	/**
	 * @var {IIdeCounterpart|null} ideConfiguration IDE counterpart component configuration
	 */
	private ideConfiguration: IIdeCounterpart|null = null

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
	 * Retrieves Daemon startup mode
	 */
	mounted(): void {
		this.getStartupMode();
	}

	/**
	 * Retrieves Daemon mode
	 */
	private getMode(): void {
		DaemonModeService.get(5000, 'gateway.mode.messages.getFailed', () => {this.msgId = null; this.loaded = true;})
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Sets new Daemon mode
	 * @param {DaemonModeEnum} newMode New Daemon mode to set
	 */
	private setMode(newMode: DaemonModeEnum): void {
		DaemonModeService.set(newMode as DaemonModeEnum, 5000, 'gateway.mode.messages.setFailed', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Daemon api response handler
	 */
	private handleResponse(response: any): void {
		this.mode = DaemonModeService.parse(response);
		if (this.mode === DaemonModeEnum.unknown) {
			this.$toast.error(
				this.$t('gateway.mode.messages.' + (this.loaded ? 'set' : 'get') + 'Failed')
					.toString()
			);
		} else if (this.loaded) {
			this.$toast.success(
				this.$t('gateway.mode.messages.' + this.mode).toString()
			);
			this.$store.dispatch('daemonStatusMode', 'unknown');
		} else {
			this.loaded = true;
		}
	}

	/**
	 * Retrieves IDE counterpart component configuration
	 */
	private getStartupMode(): Promise<void> {
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		return DaemonConfigurationService.getComponent(this.ideComponent)
			.then((response: AxiosResponse) => {
				this.ideConfiguration = response.data.instances[0];
				if (this.ideConfiguration?.operMode === undefined) {
					Object.assign(this.ideConfiguration, {operMode: this.modes.operational});
				}
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'gateway.mode.messages.startupFetchFailed',
						{error: error.response === undefined ? error.message : error.response.data.message},
					).toString()
				);
			});
	}

	/**
	 * Saves updated IDE counterpart component configuration
	 * @param {DaemonModeEnum} enum Startup mode to set
	 */
	private setStartupMode(mode: DaemonModeEnum): void {
		if (this.ideConfiguration === null || this.ideConfiguration.operMode === mode) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		this.ideConfiguration.operMode = mode;
		DaemonConfigurationService.updateInstance(this.ideComponent, this.ideConfiguration.instance, this.ideConfiguration)
			.then(() => {
				this.getStartupMode().then(() => {
					this.$toast.success(
						this.$t('gateway.mode.messages.startupSaveSuccess').toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t(
						'gateway.mode.messages.startupSaveFailed',
						{error: error.response === undefined ? error.message : error.response.data.message},
					).toString()
				);
			});
	}
}
</script>
