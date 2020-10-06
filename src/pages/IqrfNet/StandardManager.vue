<template>
	<div>
		<h1>{{ $t('iqrfnet.standard.title') }}</h1>
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
	</div>
</template>

<script lang='ts'>
import Vue from 'vue';
import BinaryOutputManager from '../../components/IqrfNet/BinaryOutputManager.vue';
import DaliManager from '../../components/IqrfNet/DaliManager.vue';
import LightManager from '../../components/IqrfNet/LightManager.vue';
import SensorManager from '../../components/IqrfNet/SensorManager.vue';

export default Vue.extend({
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
});
</script>
