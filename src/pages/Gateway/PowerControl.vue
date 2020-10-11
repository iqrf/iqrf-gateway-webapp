<template>
	<div>
		<h1>{{ $t('gateway.power.title') }}</h1>
		<CCard body-wrapper>
			<CButton color='danger' @click='powerOff()'>
				<CIcon :content='getIcon("powerStandby")' />
				{{ $t('gateway.power.powerOff.title') }}
			</CButton>
			<CButton color='primary' @click='reboot()'>
				<CIcon :content='getIcon("reload")' />
				{{ $t('gateway.power.reboot.title') }}
			</CButton>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CIcon} from '@coreui/vue/src';
import GatewayService from '../../services/GatewayService';
import {getCoreIcon} from '../../helpers/icons';
import { MetaInfo } from 'vue-meta';

@Component({
	components: {
		CButton,
		CCard,
		CIcon,
	},
	metaInfo(): MetaInfo {
		return {
			title: 'gateway.power.title',
		};
	}
})

export default class PowerControl extends Vue {
	public getIcon(icon: string): string[]|void {
		return getCoreIcon(icon);
	}

	public powerOff(): void {
		GatewayService.performPowerOff()
			.then(() => {
				this.$toast.success(
					this.$t('gateway.power.powerOff.success').toString()
				);
			});
	}

	public reboot(): void {
		GatewayService.performReboot()
			.then(() => {
				this.$toast.success(
					this.$t('gateway.power.reboot.success').toString()
				);
			});
	}
}
</script>

<style scoped>
.btn {
  margin: 0 3px 0 0;
}
</style>
