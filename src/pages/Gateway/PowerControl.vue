<template>
	<div>
		<h1>{{ $t('gateway.power.title') }}</h1>
		<CCard body-wrapper>
			<CButton color='danger' @click='powerOff()'>
				<CIcon :content='$options.icon.cilPowerStandby' />
				{{ $t('gateway.power.powerOff.title') }}
			</CButton>
			<CButton color='primary' @click='reboot()'>
				<CIcon :content='$options.icon.cilReload' />
				{{ $t('gateway.power.reboot.title') }}
			</CButton>
		</CCard>
	</div>
</template>

<script>
import {CButton, CCard, CIcon} from '@coreui/vue/src';
import { cilPowerStandby, cilReload } from '@coreui/icons';
import GatewayService from '../../services/GatewayService';

export default {
	name: 'PowerControl',
	components: {
		CButton,
		CCard,
		CIcon,
	},
	methods: {
		powerOff() {
			GatewayService.performPowerOff()
				.then(() => {
					this.$toast.success(
						this.$t('gateway.power.powerOff.success').toString()
					);
				});
		},
		reboot() {
			GatewayService.performReboot()
				.then(() => {
					this.$toast.success(
						this.$t('gateway.power.reboot.success').toString()
					);
				});
		},
	},
	icon: {
		cilPowerStandby,
		cilReload,
	},
	metaInfo: {
		title: 'gateway.power.title',
	},
};
</script>
