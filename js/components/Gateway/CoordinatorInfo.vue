<template>
	<span v-if='hasData'>
		<strong>{{ $t('gateway.info.tr.moduleType') }}: </strong> {{ trMcuType.trType }}<br>
		<strong>{{ $t('gateway.info.tr.mcuType') }}: </strong> {{ trMcuType.mcuType }}<br>
		<strong>{{ $t('gateway.info.tr.moduleId') }}: </strong> {{ osInfo.mid }}<br>
		<strong>{{ $t('gateway.info.tr.os') }}: </strong> {{ osInfo.osVersion }} ({{ osInfo.osBuild }})<br>
		<strong>{{ $t('gateway.info.tr.dpa') }}: </strong> {{ enumeration.dpaVer }}<br>
		<span v-if='enumeration.flags.rfMode'>
			<strong>{{ $t('gateway.info.tr.rfMode') }}</strong> {{ enumeration.flags.rfMode }}
		</span>
		<span v-else-if='enumeration.flags.networkType'>
			<strong>{{ $t('gateway.info.tr.networkType') }}</strong> {{ enumeration.flags.networkType }}
		</span>
		<strong>{{ $t('gateway.info.tr.voltage') }}: </strong> {{ osInfo.supplyVoltage }}<br>
	</span>
	<span v-else>
		{{ $t('gateway.info.tr.error') }}
	</span>
</template>

<script>
export default {
	name: 'CoordinatorInfo',
	data() {
		return {
			enumeration: null,
			hasData: false,
			osInfo: null,
			trMcuType: null
		};
	}
};
</script>

<style scoped>

</style>
