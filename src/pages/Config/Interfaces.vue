<template>
	<div>
		<CCard v-if='iqrfInterface === "noInterface"' body-wrapper>
			{{ $t('config.daemon.interfaces.noInterface') }}
		</CCard>
		<IqrfSpi v-if='iqrfInterface === "iqrf::IqrfSpi"' />
		<IqrfCdc v-if='iqrfInterface === "iqrf::IqrfCdc"' />
		<IqrfUart v-if='iqrfInterface === "iqrf::IqrfUart"' />
		<IqrfDpa />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard} from '@coreui/vue/src';
import IqrfSpi from '../../pages/Config/IqrfSpi.vue';
import IqrfCdc from '../../pages/Config/IqrfCdc.vue';
import IqrfUart from '../../pages/Config/IqrfUart.vue';
import IqrfDpa from '../../pages/Config/IqrfDpa.vue';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {AxiosError, AxiosResponse} from 'axios';
import {IComponent} from '../../interfaces/daemonComponent';


@Component({
	components: {
		CCard,
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
	 * @var {string} iqrfInterface Active interface
	 */
	private iqrfInterface = ''

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
	private getConfig(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getComponent('')
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.findActiveInterface(response.data.components);

			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}

	/**
	 * Finds active interface from component configuration
	 */
	private findActiveInterface(components: Array<IComponent>): void {
		const whitelist = ['iqrf::IqrfCdc', 'iqrf::IqrfSpi', 'iqrf::IqrfUart'];
		for (const component of components) {
			if (whitelist.includes(component.name) && component.enabled) {
				this.iqrfInterface = component.name;
				return;
			}
		}
		this.iqrfInterface = 'noInterface';
	}
}
</script>
