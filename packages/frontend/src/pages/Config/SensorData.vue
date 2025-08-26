<template>
	<div>
		<h1>{{ $t('config.daemon.sensor-data.title') }}</h1>
		<SensorDataForm />
	</div>
</template>

<script lang='ts'>
import { Component, Vue } from 'vue-property-decorator';
import SensorDataForm from '@/components/Config/SensorDataForm.vue';

@Component({
	components: {
		SensorDataForm,
	},
	metaInfo: {
		title: 'config.daemon.sensor-data.title'
	}
})

/**
 * Sensor data configuration page component
 */
export default class SensorData extends Vue {}
</script>
