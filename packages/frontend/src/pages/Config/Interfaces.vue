<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		<h1>{{ $t('config.daemon.interfaces.title') }}</h1>
		<v-card class='mb-5'>
			<v-card-text>
				<v-overlay
					v-if='loadFailed'
					:opacity='0.65'
					absolute
				>
					{{ $t('config.daemon.interfaces.messages.fetchFailed') }}
				</v-overlay>
				<v-select
					v-else
					v-model='iqrfInterface'
					:label='$t("config.daemon.interfaces.form.interface")'
					:items='interfaceSelect'
					:placeholder='$t("config.daemon.interfaces.form.placeholder")'
					@change='changeInterface'
				/>
			</v-card-text>
		</v-card>
		<v-card v-if='iqrfInterface === "noInterface"'>
			<v-card-text>
				{{ $t('config.daemon.interfaces.messages.noInterface') }}
			</v-card-text>
		</v-card>
		<div v-if='!loadFailed'>
			<IqrfCdc v-if='iqrfInterface === "iqrf::IqrfCdc"' @fetched='configFetched' />
			<IqrfSpi v-if='iqrfInterface === "iqrf::IqrfSpi"' @fetched='configFetched' />
			<IqrfUart v-if='iqrfInterface === "iqrf::IqrfUart"' @fetched='configFetched' />
			<IqrfDpa class='mt-5' @fetched='configFetched' />
		</div>
	</div>
</template>

<script lang='ts'>
import {
	IqrfGatewayDaemonService
} from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentConfiguration,
	IqrfGatewayDaemonComponentName,
	IqrfGatewayDaemonComponentState,
	type IqrfGatewayDaemonConfig
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError} from 'axios';

import IqrfSpi from '@/components/Config/Interfaces/IqrfSpi.vue';
import IqrfCdc from '@/components/Config/Interfaces/IqrfCdc.vue';
import IqrfUart from '@/components/Config/Interfaces/IqrfUart.vue';
import IqrfDpa from '@/components/Config/Interfaces/IqrfDpa.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {IConfigFetch} from '@/interfaces/Config/Daemon';
import {ISelectItem} from '@/interfaces/Vuetify';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		IqrfCdc,
		IqrfDpa,
		IqrfSpi,
		IqrfUart,
	},
	metaInfo: {
		title: 'config.daemon.interfaces.title',
	},
})

/**
 * Interfaces config page component
 */
export default class Interfaces extends Vue {
	/**
	 * @constant {Array<ISelectItem>} interfaceSelect Interface select options
	 */
	private interfaceSelect: Array<ISelectItem> = [
		{
			value: 'iqrf::IqrfCdc',
			text: this.$t('config.daemon.interfaces.types.cdc').toString(),
		},
		{
			value: 'iqrf::IqrfSpi',
			text: this.$t('config.daemon.interfaces.types.spi').toString(),
		},
		{
			value: 'iqrf::IqrfUart',
			text: this.$t('config.daemon.interfaces.types.uart').toString(),
		},
	];

	/**
	 * @var {string} iqrfInterface Active interface
	 */
	private iqrfInterface = '';

	/**
	 * @var {IqrfGatewayDaemonComponentConfiguration<IqrfGatewayDaemonComponentName>[]} iqrfInterfaces Array of IQRF communication interfaces
	 */
	private iqrfInterfaces: IqrfGatewayDaemonComponentConfiguration<IqrfGatewayDaemonComponentName>[] = [];

	/**
	 * @constant {Array<IqrfGatewayDaemonComponentName>} whitelist Array of IQRF communication component names
	 */
	private whitelist: Array<IqrfGatewayDaemonComponentName> = [
		IqrfGatewayDaemonComponentName.IqrfCdc,
		IqrfGatewayDaemonComponentName.IqrfSpi,
		IqrfGatewayDaemonComponentName.IqrfUart,
	];

	/**
	 * @var {Array<string>} children Array of components loading configuration
	 */
	private children: Array<string> = [
		'iface',
		'iqrfDpa'
	];

	/**
	 * @var {Array<string>} failed Array of components that failed configuration fetch
	 */
	private failed: Array<string> = [];

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false;

	/**
	 * @property {IqrfGatewayDaemonService} service IQRF Gateway Daemon configuration service
	 */
	private readonly service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves list of components
	 */
	private getConfig(): Promise<void> {
		if (!this.$store.getters['spinner/isActive']) {
			this.$store.commit('spinner/SHOW');
		}
		return this.service.getConfig()
			.then((response: IqrfGatewayDaemonConfig) => {
				this.storeInterfaces(response.components);
				this.findActiveInterface();
			})
			.catch((error: AxiosError) => {
				this.loadFailed = true;
				extendedErrorToast(error, 'config.daemon.interfaces.messages.listFailed');
			});
	}

	/**
	 * Stores IQRF communication interfaces
	 * @param {IqrfGatewayDaemonComponentConfiguration<IqrfGatewayDaemonComponentName>[]} components Array of all daemon components
	 */
	private storeInterfaces(components: IqrfGatewayDaemonComponentConfiguration<IqrfGatewayDaemonComponentName>[]): void {
		this.iqrfInterfaces = components.filter((component: IqrfGatewayDaemonComponentConfiguration<IqrfGatewayDaemonComponentName>) => (this.whitelist.includes(component.name)));
	}

	/**
	 * Sets active interface
	 */
	private findActiveInterface(): void {
		for (const component of this.iqrfInterfaces) {
			if (component.enabled) {
				this.iqrfInterface = component.name;
				return;
			}
		}
		this.iqrfInterface = 'noInterface';
	}

	/**
	 * Disables all enabled communication interfaces and enables interface selected by user
	 */
	private changeInterface(): void {
		const updateInterfaces: Array<IqrfGatewayDaemonComponentState> = [];
		for (const component of this.iqrfInterfaces) {
			if (component.name !== this.iqrfInterface && component.enabled) {
				updateInterfaces.push({name: component.name, enabled: false});
			} else if (component.name === this.iqrfInterface && !component.enabled) {
				updateInterfaces.push({name: component.name, enabled: true});
			}
		}
		this.$store.commit('spinner/SHOW');
		this.service.updateEnabledComponents(updateInterfaces)
			.then(() => {
				this.getConfig().then(() =>
					this.$toast.success(
						this.$t('config.daemon.interfaces.messages.updateSuccess', {interface: this.interfaceCode(this.iqrfInterface)}).toString()
					)
				);
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.interfaces.messages.updateFailed');
			});
	}

	/**
	 * Returns interface abbreviation from component name
	 * @param {string} iqrfInterface Iqrf interface component name
	 * @returns {string} Interface abbreviation
	 */
	private interfaceCode(iqrfInterface: string): string {
		if (iqrfInterface === 'iqrf::IqrfCdc') {
			return 'CDC';
		} else if (iqrfInterface === 'iqrf::IqrfSpi') {
			return 'SPI';
		} else if (iqrfInterface === 'iqrf::IqrfUart') {
			return 'UART';
		}
		return '';
	}

	/**
	 * Handles configuration fetch events
	 */
	private configFetched(data: IConfigFetch): void {
		if (['iqrfCdc', 'iqrfSpi', 'iqrfUart'].includes(data.name)) {
			this.children = this.children.filter((item: string) => item !== 'iface');
		} else {
			this.children = this.children.filter((item: string) => item !== data.name);
		}
		if (!data.success) {
			this.failed.push(this.$t(`config.daemon.interfaces.${data.name}.title`).toString());
		}
		if (this.children.length > 0) {
			return;
		}
		this.$store.commit('spinner/HIDE');
		if (this.failed.length === 0) {
			return;
		}
		this.$toast.error(
			this.$t(
				'config.daemon.messages.configFetchFailed',
				{children: this.failed.sort().join(', ')},
			).toString()
		);
		this.failed = [];
	}

}
</script>
