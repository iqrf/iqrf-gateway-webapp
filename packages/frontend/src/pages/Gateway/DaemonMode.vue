<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<div>
		<h1>{{ $t('gateway.mode.title') }}</h1>
		<CCard body-wrapper>
			<CRow align-vertical='center'>
				<CCol md='2'>
					<strong>{{ $t('gateway.info.gwMode') }}</strong>
				</CCol>
				<CCol>
					<CDropdown
						color='primary'
						:toggler-text='$t(`gateway.mode.modes.${mode}`)'
						placement='bottom'
					>
						<CDropdownItem
							@click='setMode(modes.operational)'
						>
							{{ $t('gateway.mode.modes.operational') }}
						</CDropdownItem>
						<CDropdownItem
							@click='setMode(modes.service)'
						>
							{{ $t('gateway.mode.modes.service') }}
						</CDropdownItem>
						<CDropdownItem
							@click='setMode(modes.forwarding)'
						>
							{{ $t('gateway.mode.modes.forwarding') }}
						</CDropdownItem>
					</CDropdown>
				</CCol>
			</CRow>
			<CRow
				v-if='ideConfiguration !== null'
				class='mt-4'
				align-vertical='center'
			>
				<CCol md='2'>
					<strong>{{ $t('gateway.mode.startupMode') }}</strong>
				</CCol>
				<CCol>
					<CDropdown
						color='primary'
						:toggler-text='$t(`gateway.mode.modes.${ideConfiguration.operMode}`)'
						placement='bottom'
					>
						<CDropdownItem
							@click='setStartupMode(modes.operational)'
						>
							{{ $t('gateway.mode.modes.operational') }}
						</CDropdownItem>
						<CDropdownItem
							@click='setStartupMode(modes.service)'
						>
							{{ $t('gateway.mode.modes.service') }}
						</CDropdownItem>
						<CDropdownItem
							@click='setStartupMode(modes.forwarding)'
						>
							{{ $t('gateway.mode.modes.forwarding') }}
						</CDropdownItem>
					</CDropdown>
				</CCol>
			</CRow>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CDropdown, CDropdownItem} from '@coreui/vue/src';

import {buildDaemonMessageOptions} from '@/store/modules/daemonClient.module';
import {DaemonModeEnum} from '@/enums/Gateway/DaemonMode';
import {extendedErrorToast} from '@/helpers/errorToast';
import DaemonConfigurationService from '@/services/DaemonConfigurationService';
import ManagementService from '@/services/DaemonApi/ManagementService';

import {AxiosError, AxiosResponse} from 'axios';
import {MutationPayload} from 'vuex';
import {IIdeCounterpart} from '@/interfaces/Config/IdeCounterpart';
import {DaemonClientState} from '@/interfaces/wsClient';

@Component({
	components: {
		CButton,
		CCard,
		CDropdown,
		CDropdownItem
	},
	data: () => ({
		DaemonModeEnum,
	}),
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
	];

	/**
	 * @constant {string} component IDE counterpart component name
	 */
	private ideComponent = 'iqrf::IdeCounterpart';

	/**
	 * @var {IIdeCounterpart|null} ideConfiguration IDE counterpart component configuration
	 */
	private ideConfiguration: IIdeCounterpart|null = null;

	/**
	 * @var {boolean} loaded Auxiliary property to help choose correct message
	 */
	private loaded = false;

	/**
	 * @var {DaemonModeEnum} mode Current Daemon mode
	 */
	private mode: DaemonModeEnum = DaemonModeEnum.unknown;

	/**
	 * @constant {DaemonModeEnum} modes Daemon mode options
	 */
	private modes: Record<string, DaemonModeEnum> = {
		forwarding: DaemonModeEnum.forwarding,
		operational: DaemonModeEnum.operational,
		service: DaemonModeEnum.service
	};

	/**
	 * @var {string} msgId Daemon api message id
	 */
	private msgId = '';

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Component unwatch function
	 */
	private unwatch: CallableFunction = () => {return;};

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONMESSAGE') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				if (mutation.payload.data.msgId === this.msgId) {
					this.$store.dispatch('daemonClient/removeMessage', this.msgId);
					this.handleModeResponse(mutation.payload);
				}
			}
		});
		if (this.$store.getters['daemonClient/isConnected']) {
			this.getMode();
		} else {
			this.unwatch = this.$store.watch(
				(state: DaemonClientState, getter) => getter['daemonClient/isConnected'],
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
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
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
		const options = buildDaemonMessageOptions(5000, 'gateway.mode.messages.getFailed', () => {this.msgId = ''; this.loaded = true;});
		ManagementService.getMode(options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Sets Daemon mode
	 * @param {DaemonModeEnum} mode Daemon mode to set
	 */
	private setMode(mode: DaemonModeEnum): void {
		const options = buildDaemonMessageOptions(5000, 'gateway.mode.messages.setFailed', () => this.msgId = '');
		ManagementService.setMode(mode, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Daemon API mode response
	 * @param response Daemon API mode response
	 */
	private handleModeResponse(response): void {
		this.mode = ManagementService.parseModeResponse(response);
		if (this.mode === DaemonModeEnum.unknown) {
			let errorMessage: string;
			if (this.loaded) {
				errorMessage = this.$t('gateway.mode.messages.setFailed').toString();
			} else {
				errorMessage = this.$t('gateway.mode.messages.getFailed').toString();
			}
			this.$toast.error(errorMessage);
		} else if (this.loaded) {
			this.$toast.success(
				this.$t(`gateway.mode.messages.${this.mode}`).toString()
			);
			this.$store.dispatch('monitorClient/setMode', 'unknown');
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
				if (this.ideConfiguration !== null && this.ideConfiguration.operMode === undefined) {
					this.ideConfiguration.operMode = this.modes.operational;
				}
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'gateway.mode.messages.startupFetchFailed'));
	}

	/**
	 * Saves updated IDE counterpart component configuration
	 * @param {DaemonModeEnum} mode Startup mode to set
	 */
	private setStartupMode(mode: DaemonModeEnum): void {
		if (this.ideConfiguration === null || this.ideConfiguration.operMode === mode) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		const configuration = {...this.ideConfiguration};
		configuration.operMode = mode;
		DaemonConfigurationService.updateInstance(this.ideComponent, configuration.instance, configuration)
			.then(() => {
				this.getStartupMode().then(() => {
					this.$toast.success(
						this.$t('gateway.mode.messages.startupSaveSuccess').toString()
					);
				});
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'gateway.mode.messages.startupSaveFailed'));
	}

}
</script>
