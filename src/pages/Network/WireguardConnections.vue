<template>
	<div>
		<h1>{{ $t('network.wireguard.title') }}</h1>
		<CCard>
			<CCardBody
				v-if='interfaces !== null && interfaces.length === 0'
			>
				{{ $t('network.wireguard.messages.noInterfaces') }}
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>

import {Component, Vue} from 'vue-property-decorator';
import {CCard} from '@coreui/vue/src';

import NetworkInterfaceService, {InterfaceType} from '../../services/NetworkInterfaceService';

import {AxiosResponse} from 'axios';
import {NetworkInterface} from '../../interfaces/network';

@Component({
	components: {
		CCard,
	},
	metaInfo: {
		title: 'network.wireguard.title'
	}
})

/**
 * WireGuard connections component
 */
export default class WireGuardConnections extends Vue {

	/**
	 * @var {Array<NetworkInterface>|null} interfaces Array of existing interfaces
	 */
	private interfaces: Array<NetworkInterface>|null = null

	/**
	 * Retrieves existing wireguard interfaces
	 */
	mounted(): void {
		this.$store.commit('spinner/SHOW');
		NetworkInterfaceService.list(InterfaceType.WIREGUARD)
			.then((response: AxiosResponse) => {
				this.interfaces = (response.data as Array<NetworkInterface>);
				if (this.interfaces.length > 0) {
					//
				}
				this.$store.commit('spinner/HIDE');
			});
	}
}
</script>