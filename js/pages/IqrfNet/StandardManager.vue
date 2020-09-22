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
import BinaryOutputManager from '../../components/IqrfNet/BinaryOutputManager';
import DaliManager from '../../components/IqrfNet/DaliManager';
import LightManager from '../../components/IqrfNet/LightManager';
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
