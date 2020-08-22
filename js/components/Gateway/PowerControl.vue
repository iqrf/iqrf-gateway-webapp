<template>
	<div class='panel panel-default'>
		<div class='panel-body'>
			<button class='btn btn-danger' @click='powerOff()'>
				<span class='glyphicon glyphicon-off'></span>
				{{ $t('gateway.power.powerOff.title') }}
			</button>
			<button class='btn btn-primary' @click='reboot()'>
				<span class='glyphicon glyphicon-repeat'></span>
				{{ $t('gateway.power.reboot.title') }}
			</button>
		</div>
	</div>
</template>

<script>
import GatewayService from '../../services/GatewayService';

export default {
	name: 'PowerControl',
	methods: {
		powerOff() {
			GatewayService.performPowerOff()
				.then(() => {
					this.$toast.success(this.$t('gateway.power.powerOff.success'));
				});
		},
		reboot() {
			GatewayService.performReboot()
				.then(() => {
					this.$toast.success(this.$t('gateway.power.reboot.success'));
				});
		},
	},
};
</script>

<style scoped>

</style>
