<template>
	<CRow>
		<CCol sm='6'>
			<BinaryOutputManager />
			<SensorManager />
		</CCol>
		<CCol sm='6'>
			<LightManager />
			<DaliManager />
		</CCol>
	</CRow>
</template>

<script>
import BinaryOutputManager from './BinaryOutputManager';
import DaliManager from './DaliManager';
import LightManager from './LightManager';
import SensorManager from './SensorManager';

export default {
	name: 'StandardManager',
	components: {
		BinaryOutputManager,
		DaliManager,
		LightManager,
		SensorManager,
	},
	data() {
		return {};
	}
};
</script>
