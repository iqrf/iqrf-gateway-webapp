<template>
	<div>
		<h1>{{ $t('network.wireless.hotspot.title') }}</h1>
		<CCard>
			<CCardBody>
				<CForm>
					<CSelect
						:value.sync='hotspot.interface'
						:options='interfaceOptions'
						:label='$t("network.connection.interface")'
					/>
					<CInput
						v-model='hotspot.ssid'
						:label='$t("network.wireless.table.ssid")'
					/>
					<CInput
						v-model='hotspot.password'
						:label='$t("network.wireless.form.password")'
					/>
					<CButton
						color='primary'
						@click='createHotspot'
					>
						{{ $t('network.wireless.hotspot.create') }}
					</CButton>
				</CForm>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput, CSelect} from '@coreui/vue/src';

import NetworkInterfaceService, {InterfaceType} from '../../services/NetworkInterfaceService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '../../interfaces/coreui';
import {IHotspot, NetworkInterface} from '../../interfaces/network';
import NetworkConnectionService from '../../services/NetworkConnectionService';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CSelect,
	},
})

/**
 * Wireless hotspot creation component
 */
export default class HotspotForm extends Vue {

	/**
	 * @var {IHotspot} hotspot Hotspot configuration
	 */
	private hotspot: IHotspot = {
		interface: '',
		ssid: '',
		password: '',
	}

	/**
	 * @var {Array<IOption>} interfaceOptions Array of CoreUI select options for wifi interface
	 */
	private interfaceOptions: Array<IOption> = []

	mounted(): void {
		this.getInterfaces();
	}

	/**
	 * Retrieves list of wifi interfaces
	 */
	private getInterfaces(): void {
		NetworkInterfaceService.list(InterfaceType.WIFI)
			.then((response: AxiosResponse) => {
				let interfaces: Array<IOption> = [];
				response.data.forEach((item: NetworkInterface) => {
					interfaces.push({label: item.name, value: item.name});
				});
				this.interfaceOptions = interfaces;
			})
			.catch((error: AxiosError) => {
				//
			});
	}

	/**
	 * Creates a hotspot
	 */
	private createHotspot(): void {
		NetworkConnectionService.createHotspot(this.hotspot)
			.then((response: AxiosResponse) => {
				//
			})
			.catch((error: AxiosError) => {
				//
			});
	}

}
</script>
