<template>
	<div>
		<h1>{{ $t('network.wireguard.title') }}</h1>
		<CRow>
			<CCol md='6'>
				<WireguardConnections />
				<WireguardKeys />
			</CCol>
		</CRow>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCol, CRow} from '@coreui/vue/src';
import WireguardConnections from '../../components/Network/WireguardConnections.vue';
import WireguardKeys from '../../components/Network/WireguardKeys.vue';

@Component({
	components: {
		CCol,
		CRow,
		WireguardConnections,
		WireguardKeys,
	},
	metaInfo: {
		title: 'network.wireguard.title'
	}
})

/**
 * WireGuard VPN component
 */
export default class WireGuard extends Vue {

}
</script>