<template>
	<div>
		<h1>{{ $t('gateway.troubleshooter.title') }}</h1>
		<CCard>
			<CCardBody>
				<table class='table'>
					<tbody>
						<tr>
							<th>{{ $t('gateway.troubleshooter.table.headers.item') }}</th>
							<th>{{ $t('gateway.troubleshooter.table.headers.state') }}</th>
							<th>{{ $t('gateway.troubleshooter.table.headers.action') }}</th>
						</tr>
						<tr v-for='entry of entries' :key='entries.indexOf(entry)'>
							<th>{{ entry.item }}</th>
							<td>{{ entry.state }}</td>
							<td>{{ entry.action }}</td>
						</tr>
					</tbody>
				</table>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody} from '@coreui/vue/src';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import LedService from '../../services/DaemonApi/LedService';
import {AxiosError, AxiosResponse} from 'axios';
import {IComponent} from '../../interfaces/daemonComponent';
import {Dictionary} from 'vue-router/types/router';
import {MutationPayload} from 'vuex';
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';
import { IIqrfRepository } from '../../interfaces/iqrfRepository';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
	},
	metaInfo: {
		title: 'gateway.troubleshooter.title',
	},
})

/**
 * Gateway Troubleshooter component
 */
export default class Troubleshooter extends Vue {
	/**
	 * @var {Array<Dictionary<string>>}
	 */
	private entries: Array<Dictionary<string>> = []
	
	/**
	 * @var {Dictionary<boolean>} interfaces Dictionary of IQRF interfaces
	 */
	private interfaces: Dictionary<boolean> = {
		'iqrf::IqrfCdc': false,
		'iqrf::IqrfSpi': false,
		'iqrf::IqrfUart': false,
	}

	/**
	 * @var {string|null} msgId Daemon API message ID
	 */
	private msgId: string|null = null

	/**
	 * @constant {Array<string>} whitelistedComponents Array of IQRF interface components for filtering
	 */
	private whitelistedComponents: Array<string> = [
		'iqrf::IqrfCdc',
		'iqrf::IqrfSpi',
		'iqrf::IqrfUart',
	]

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId === this.msgId) {
				this.$store.dispatch('removeMessage', this.msgId);
				this.handlePulseResponse(mutation.payload);
			}
		});
	}
	
	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getInterfaces();
		this.getCache();
	}

	beforeDestroy(): void {
		this.$store.dispatch('removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Retrieves active IQRF interfaces
	 */
	private getInterfaces(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getComponent('')
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				for (const component of response.data.components as Array<IComponent>) {
					if (!this.whitelistedComponents.includes(component.name)) {
						continue;
					}
					this.interfaces[component.name] = component.enabled;
				}
				this.interfaceEntry();
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}

	/**
	 * Creates the interface entry with current state and suggested actions if an action is necessary
	 */
	private interfaceEntry(): void {
		let messageTokens: Array<string> = [];
		for (const [key, value] of Object.entries(this.interfaces)) {
			if (value) {
				messageTokens.push(key);
			}
		}
		let entry = {
			item: this.$t('gateway.troubleshooter.table.interface').toString(),
			state: messageTokens.join(', ')
		};
		let action = this.$t('gateway.troubleshooter.actions.noAction').toString();
		if (messageTokens.length === 0) {
			Object.assign(entry, {state: this.$t('gateway.troubleshooter.states.noInterface').toString()});
			action = this.$t('gateway.troubleshooter.actions.noInterface').toString();
		} else if (messageTokens.length > 1) {
			action = this.$t('gateway.troubleshooter.actions.multipleInterfaces').toString();
		}
		Object.assign(entry, {action: action});
		this.entries.push(entry);
	}

	/**
	 * Retrieves JsCache component configuration
	 */
	private getCache(): void {
		DaemonConfigurationService.getComponent('iqrf::JsCache')
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.cacheEntry(response.data.instances[0]);
				} else {
					this.entries.push({
						item: this.$t('gateway.troubleshooter.table.repository').toString(),
						state: this.$t('gateway.troubleshooter.states.noCacheInstance').toString(),
						action: this.$t('gateway.troubleshooter.actions.addCacheInstance').toString()
					});
				}
				
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
		this.sendPulse();
	}

	/**
	 * Creates a cache entry for the table
	 * @param {IIqrfRepository} instance JsCache component instance configuration
	 */
	private cacheEntry(instance: IIqrfRepository): void {
		let entry = {
			item: this.$t('gateway.troubleshooter.table.repositoryDownload').toString(),
		};
		if (instance.downloadIfRepoCacheEmpty) {
			Object.assign(entry, {
				state: this.$t('gateway.troubleshooter.states.cacheDownloadEnabled').toString(),
				action: this.$t('gateway.troubleshooter.actions.noAction').toString()
			});
		} else {
			Object.assign(entry, {
				state: this.$t('gateway.troubleshooter.states.cacheDownloadDisabled').toString(),
				action: this.$t('gateway.troubleshooter.actions.enableDownload').toString()
			});
		}
		this.entries.push(entry);
	}

	/**
	 * Sends a LEDR pulse message to check if JsCache is loaded properly
	 */
	private sendPulse(): void {
		LedService.pulseLedr(0, new WebSocketOptions(null, 10000, null, () => this.msgId = null))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles LEDR pulse Daemon API response
	 */
	private handlePulseResponse(response): void {
		let entry = {
			item: this.$t('gateway.troubleshooter.table.repository').toString()
		};
		if (response.data.status === 0) {
			Object.assign(entry, {
				state: this.$t('gateway.troubleshooter.states.cacheLoaded').toString(),
				action: this.$t('gateway.troubleshooter.actions.noAction').toString()
			});
		} else if (response.data.status === -1 && response.mType === 'messageError') {
			Object.assign(entry, {
				state: this.$t('gateway.troubleshooter.states.cacheNotLoaded').toString(),
				action: this.$t('gateway.troubleshooter.actions.fixCache').toString()
			});
		}
		this.entries.push(entry);
	}
}
</script>

<style scoped>
table {
    table-layout: fixed;
}
</style>
