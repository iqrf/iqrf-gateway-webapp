<template>
	<span v-if='hasData'>
		<strong>{{ $t('gateway.info.tr.moduleType') }}: </strong>
		{{ trMcuType.trType }}<br>
		<strong>{{ $t('gateway.info.tr.mcuType') }}: </strong>
		{{ trMcuType.mcuType }}<br>
		<strong>{{ $t('gateway.info.tr.moduleId') }}: </strong> {{ osInfo.mid }}<br>
		<strong>{{ $t('gateway.info.tr.os') }}: </strong>
		{{ osInfo.osVersion }} ({{ osInfo.osBuild }})<br>
		<strong>{{ $t('gateway.info.tr.dpa') }}: </strong>
		{{ enumeration.dpaVer }}<br>
		<span v-if='enumeration.flags.rfMode'>
			<strong>{{ $t('gateway.info.tr.rfMode') }}</strong>
			{{ enumeration.flags.rfMode }}
		</span>
		<span v-else-if='enumeration.flags.networkType'>
			<strong>{{ $t('gateway.info.tr.networkType') }}</strong>
			{{ enumeration.flags.networkType }}
		</span>
		<strong>{{ $t('gateway.info.tr.voltage') }}: </strong>
		{{ osInfo.supplyVoltage }}<br>
	</span>
	<span v-else>
		{{ $t('gateway.info.tr.error') }}
	</span>
</template>

<script>
import IqmeshNetworkService from '../../services/IqmeshNetworkService';
export default {
	name: 'CoordinatorInfo',
	data() {
		return {
			enumeration: null,
			hasData: false,
			osInfo: null,
			trMcuType: null
		};
	},
	created() {
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type !== 'SOCKET_ONMESSAGE' ||
					mutation.payload.mType !== 'iqmeshNetwork_EnumerateDevice') {
				return;
			}
			try {
				const data = mutation.payload.data.rsp;
				this.enumeration = data.peripheralEnumeration;
				this.osInfo = data.osRead;
				this.trMcuType = this.osInfo.trMcuType;
				this.hasData = true;
			} catch (e) {
				this.hasData = false;
			}
		});
		IqmeshNetworkService.enumerateDevice(0);
	},
	beforeDestroy() {
		this.unsubscribe();
	},
};
</script>

<style scoped>

</style>
