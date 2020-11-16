<template>
	<div>
		<h1>{{ $t('config.daemon.interfaces.title') }}</h1>
		<CCard body-wrapper>
			<CSelect
				:value.sync='iqrfInterface'
				:label='$t("config.daemon.interfaces.form.interface")'
				:options='interfaceSelect'
				:placeholder='$t("config.daemon.interfaces.form.placeholder")'
				@change='changeInterface'
			/>
		</CCard>
		<CCard v-if='iqrfInterface === "noInterface"' body-wrapper>
			{{ $t('config.daemon.interfaces.messages.noInterface') }}
		</CCard>
		<IqrfSpi v-if='iqrfInterface === "iqrf::IqrfSpi"' />
		<IqrfCdc v-if='iqrfInterface === "iqrf::IqrfCdc"' />
		<IqrfUart v-if='iqrfInterface === "iqrf::IqrfUart"' />
		<IqrfDpa />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader, CSelect} from '@coreui/vue/src';
import IqrfSpi from '../../components/Config/IqrfSpi.vue';
import IqrfCdc from '../../components/Config/IqrfCdc.vue';
import IqrfUart from '../../components/Config/IqrfUart.vue';
import IqrfDpa from '../../components/Config/IqrfDpa.vue';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {AxiosError, AxiosResponse} from 'axios';
import {IComponent} from '../../interfaces/daemonComponent';
import { IOption } from '../../interfaces/coreui';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
		CSelect,
		IqrfCdc,
		IqrfDpa,
		IqrfSpi,
		IqrfUart
	},
	metaInfo: {
		title: 'config.daemon.interfaces.title'
	}
})

/**
 * Interfaces config page component
 */
export default class Interfaces extends Vue {
	/**
	 * @var {boolean} powerUser Indicates whether the user account is advanced 
	 */
	private powerUser = false;

	/**
	 * @constant {Array<IOption>} interfaceSelect Array of CoreUI select options for interface
	 */
	private interfaceSelect: Array<IOption> = [
		{
			value: 'iqrf::IqrfCdc',
			label: this.$t('config.daemon.interfaces.types.cdc').toString(),
		},
		{
			value: 'iqrf::IqrfSpi',
			label: this.$t('config.daemon.interfaces.types.spi').toString(),
		},
		{
			value: 'iqrf::IqrfUart',
			label: this.$t('config.daemon.interfaces.types.uart').toString(),
		},
	]

	/**
	 * @var {string} iqrfInterface Active interface
	 */
	private iqrfInterface = ''

	/**
	 * @var {Array<IComponent>} iqrfInterfaces Array of IQRF communication interfaces
	 */
	private iqrfInterfaces: Array<IComponent> = []

	/**
	 * @constant {Array<string>} whitelist Array of IQRF communication component names
	 */
	private whitelist: Array<string> = [
		'iqrf::IqrfCdc',
		'iqrf::IqrfSpi',
		'iqrf::IqrfUart',
	]

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		if (this.$store.getters['user/getRole'] === 'power') {
			this.powerUser = true;
		}
		this.getConfig();
	}

	/**
	 * Retrieves list of components
	 */
	private getConfig(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return DaemonConfigurationService.getComponent('')
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.storeInterfaces(response.data.components);
				this.findActiveInterface();
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}

	/**
	 * Stores IQRF communication interfaces
	 * @param {Array<IComponent>} components Array of all daemon components
	 */
	private storeInterfaces(components: Array<IComponent>): void {
		let interfaces: Array<IComponent> = [];
		for (const component of components) {
			if (this.whitelist.includes(component.name)) {
				interfaces.push(component);
			}
		}
		this.iqrfInterfaces = interfaces;
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
		let requests: Array<Promise<AxiosResponse>> = [];
		for (let component of this.iqrfInterfaces) {
			if (component.name !== this.iqrfInterface && component.enabled) {
				component.enabled = false;
				requests.push(DaemonConfigurationService.updateComponent(component.name, component));
			} else if (component.name === this.iqrfInterface && !component.enabled) {
				component.enabled = true;
				requests.push(DaemonConfigurationService.updateComponent(component.name, component));
			}
		}
		Promise.all(requests)
			.then(() => this.getConfig().then(() => this.$toast.success(
				this.$t('config.daemon.interfaces.messages.updateSuccess', {interface: this.interfaceCode(this.iqrfInterface)}).toString()	
			)))
			.catch((error: AxiosError) => {
				console.error(error);
				this.$toast.error(
					this.$t('config.daemon.interfaces.messages.updateFailed').toString()
				);
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
		} else {
			return 'UART';
		}
	}
}
</script>
