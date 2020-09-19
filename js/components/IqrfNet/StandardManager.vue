<template>
	<CRow>
		<CCol md='6'>
			<BinaryOutputManager />
			<SensorManager />
		</CCol>
		<CCol md='6'>
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
	metaInfo: {
		title: 'iqrfnet.standard.title',
	},
};
</script>
