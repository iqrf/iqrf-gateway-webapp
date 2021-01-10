<template>
	<div>
		<h1>{{ $t('gateway.troubleshooter.title') }}</h1>
		<CCard>
			<CCardBody>
				<table class='table'>
					<tbody>
						<tr class='table-header'>
							<th>{{ $t('gateway.troubleshooter.table.headers.item') }}</th>
							<th>{{ $t('gateway.troubleshooter.table.headers.state') }}</th>
							<th>{{ $t('gateway.troubleshooter.table.headers.action') }}</th>
						</tr>
					</tbody>
				</table>
				<table class='table'>
					<tbody>
						<tr>
							<th colspan='3' class='table-subheader'>
								{{ $t('gateway.troubleshooter.table.daemon.title') }}
							</th>
						</tr>
						<tr v-for='entry of daemon' :key='daemon.indexOf(entry)'>
							<th>{{ entry.item }}</th>
							<td style='white-space:pre'>{{ entry.state }}</td>
							<td>{{ entry.action }}</td>
						</tr>
					</tbody>
				</table>
				<table v-for='feature of features' :key='features.indexOf(feature)' class='table'>
					<tbody>
						<tr>
							<th colspan='3' class='table-subheader'>
								{{ feature.title }}
							</th>
						</tr>
						<tr v-for='entry of feature.entries' :key='feature.entries.indexOf(entry)'>
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
import GatewayService from '../../services/GatewayService';
import LedService from '../../services/DaemonApi/LedService';
import {AxiosError, AxiosResponse} from 'axios';
import {MutationPayload} from 'vuex';
import {WebSocketOptions} from '../../store/modules/webSocketClient.module';
import {IIqrfRepository} from '../../interfaces/iqrfRepository';
import {IEntry, IDaemonConfig, IFeature, IFeatureData} from '../../interfaces/troubleshooter';

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
	 * @var {Array<IEntry>} daemon IQRF GW Daemon troubleshooting information
	 */
	private daemon: Array<IEntry> = []

	private featureNames = {	
		'controller': 'iqrfGatewayController',
		'translator': 'iqrfGatewayTranslator',
		'mender': 'mender'
	}

	/**
	 * @var features Features troubleshooting information
	 */
	private features: Array<IFeature> = []

	/**
	 * @var {string|null} msgId Daemon API message ID
	 */
	private msgId: string|null = null

	/**
	 * @constant {Array<number>} whitelistedPermissions Array of correct configuration file permissions
	 */
	private filePermissions: Array<number> = [
		644, 666
	]

	/**
	 * @constant {number} dirPermission Directory permission
	 */
	private dirPermission = 777

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
		this.getCache();
		this.troubleshoot();
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('removeMessage', this.msgId);
		this.unsubscribe();
	}

	private troubleshoot(): void {
		GatewayService.troubleshoot()
			.then((response: AxiosResponse) => {
				this.interfaceEntry(response.data.daemon.interfaces);
				this.daemonConfigEntry(response.data.daemon.config);
				this.parseFeatures(response.data.features);
			})
			.catch((error: AxiosError) => console.error(error));
	}

	/**
	 * Creates the interface entry with current state and suggested actions if an action is necessary
	 */
	private interfaceEntry(interfaces: any): void {
		let entry: IEntry = {
			item: this.$t('gateway.troubleshooter.table.daemon.interface').toString(),
			state: '',
			action: this.$t('gateway.troubleshooter.actions.noAction').toString()
		};
		if (interfaces.error !== undefined) {
			entry.state = interfaces.error;
		} else {
			entry.state = interfaces.join(', ');
			if (interfaces.length === 0) {
				entry.state = this.$t('gateway.troubleshooter.states.noInterface').toString(),
				entry.action = this.$t('gateway.troubleshooter.actions.noInterface').toString();
			} else if (interfaces.length > 1) {
				entry.action = this.$t('gateway.troubleshooter.actions.multipleInterfaces').toString();
			}
		}
		this.daemon.push(entry);
	}

	/**
	 * Creates Daemon configuration files troubleshoot entry
	 * @param {Array<IDaemonConfig>} files Array of daemon configuration file metadata
	 */
	private daemonConfigEntry(files: Array<IDaemonConfig>): void {
		let entry: IEntry = {
			item: this.$t('gateway.troubleshooter.table.features.config').toString(),
			state: this.$t('gateway.troubleshooter.states.configCorrect').toString(),
			action: this.$t('gateway.troubleshooter.actions.noAction').toString()
		};
		let wrongFiles: Array<string> = [];
		for (const file of files) {
			if (file.dir && file.permission !== this.dirPermission) {
				wrongFiles.push('dir ' + file.name + ': ' + file.permission);
			} else if (!file.dir && !this.filePermissions.includes(file.permission)) {
				wrongFiles.push('file ' + file.name + ': ' + file.permission);
			}
		}
		if (wrongFiles.length > 0) {
			entry.state = this.$t(
				'gateway.troubleshooter.states.configInvalidPermission'
			).toString() + '\n' + wrongFiles.join('\n');
			entry.action = this.$t('gateway.troubleshooter.actions.configFixPermission').toString();
		}
		this.daemon.push(entry);
	}

	/**
	 * Creates troubleshoot entries for features
	 * @param {Array<IFeatureData>} features Array of feature data
	 */
	private parseFeatures(features: Array<IFeatureData>): void {
		let parsedFeatures: Array<IFeature> = [];
		for (const item of features) {
			let feature: IFeature = {
				title: this.$t('gateway.troubleshooter.table.features.' + item.name).toString(),
				entries: []
			};
			let entryArray: Array<IEntry> = [];
			entryArray.push(this.featureStatus(item));
			if (item.installed) {
				entryArray.push(this.featureConfiguration(item));
			}
			feature.entries = entryArray;
			parsedFeatures.push(feature);
		}
		this.features = parsedFeatures;
	}

	/**
	 * Creates feature status entry
	 * @param {string} name Feature name
	 * @param {IFeatureData} feature Feature data from REST API
	 * @returns {IEntry} Feature status entry
	 */
	private featureStatus(feature: IFeatureData): IEntry {
		let status = {
			item: this.$t('gateway.troubleshooter.table.features.status').toString(),
			action: this.$t('gateway.troubleshooter.actions.noAction').toString(),
			state: ''
		};
		if (feature.installed) {
			status.state += this.$t('gateway.troubleshooter.states.installed').toString();
			status.state += ', ' + this.$t('gateway.troubleshooter.states.' + (this.featureEnabled(feature.name) ? 'enabled': 'notEnabled')).toString();
		} else {
			status.state += this.$t('gateway.troubleshooter.states.notInstalled').toString();
			if (this.featureEnabled(feature.name)) {
				status.state += ', ' + this.$t('gateway.troubleshooter.states.enabled').toString();
				status.action = this.$t('gateway.troubleshooter.actions.enabledNotInstalled').toString();
			}
		}
		return status;
	}

	/**
	 * Creates feature configuration entry
	 * @param {IFeature} feature Feature object
	 * @returns {IEntry} Feature configuration entry
	 */
	private featureConfiguration(feature: IFeatureData): IEntry {
		let configuration = {
			item: this.$t('gateway.troubleshooter.table.features.config').toString(),
			state: this.$t('gateway.troubleshooter.states.configCorrect').toString(),
			action: this.$t('gateway.troubleshooter.actions.noAction').toString()
		};
		if (!feature.config) {
			configuration.state = this.$t('gateway.troubleshooter.states.configMissing').toString();
			configuration.action = this.$t('gateway.troubleshooter.action.configMissing').toString();
		} else if (!this.filePermissions.includes(feature.permission)) {
			configuration.state = this.$t('gateway.troubleshooter.states.configInvalidPermission', {permission: feature.permission}).toString();
			configuration.action = this.$t('gateway.troubleshooter.actions.configFixPermission').toString();
		}
		return configuration;
	}

	/**
	 * Checks whether feature is enabled
	 * @param {string} name Feature name
	 * @returns {boolean} True if enabled, false otherwise
	 */
	private featureEnabled(name: string): boolean {
		return this.$store.getters['features/isEnabled'](this.featureNames[name]);
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
					this.daemon.push({
						item: this.$t('gateway.troubleshooter.table.daemon.repository').toString(),
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
		let entry: IEntry = {
			item: this.$t('gateway.troubleshooter.table.daemon.repositoryDownload').toString(),
			state: '',
			action: this.$t('gateway.troubleshooter.actions.noAction').toString()
		};
		if (instance.downloadIfRepoCacheEmpty) {
			entry.state = this.$t('gateway.troubleshooter.states.cacheDownloadEnabled').toString();
		} else {
			entry.state = this.$t('gateway.troubleshooter.states.cacheDownloadDisabled').toString();
			entry.action = this.$t('gateway.troubleshooter.actions.enableDownload').toString();
		}
		this.daemon.push(entry);
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
		let entry: IEntry = {
			item: this.$t('gateway.troubleshooter.table.daemon.repository').toString(),
			state: '',
			action: this.$t('gateway.troubleshooter.actions.noAction').toString()
		};
		if (response.data.status === 0) {
			entry.state = this.$t('gateway.troubleshooter.states.cacheLoaded').toString();
		} else if (response.data.status === -1 || response.mType === 'messageError') {
			entry.state = this.$t('gateway.troubleshooter.states.cacheNotLoaded').toString();
			entry.action = this.$t('gateway.troubleshooter.actions.fixCache').toString();
		}
		this.daemon.push(entry);
	}
}
</script>

<style scoped>
table {
    table-layout: fixed;
}

.table-header {
	border-top: 3px solid lightgray;
	border-bottom: 3px solid lightgray;
}

.table-subheader {
	border-top: none;
	border-bottom: 2px solid lightgray;
}

</style>
