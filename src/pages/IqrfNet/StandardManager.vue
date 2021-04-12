<template>
	<div>
		<h1>{{ $t('iqrfnet.standard.title') }}</h1>
		<CRow>
			<CCol md='6'>
				<CCard>
					<CTabs variant='tabs'>
						<CTab :title='$t("iqrfnet.standard.sensor.title")'>
							<SensorManager />
						</CTab>
						<CTab :title='$t("iqrfnet.standard.binaryOutput.title")'>
							<BinaryOutputManager />
						</CTab>
						<CTab :title='$t("iqrfnet.standard.light.title")'>
							<LightManager />
						</CTab>
						<CTab :title='$t("iqrfnet.standard.dali.title")'>
							<DaliManager />
						</CTab>
					</CTabs>
				</CCard>
			</CCol>
			<CCol md='6'>
				<StandardGrid />
			</CCol>
		</CRow>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CTab, CTabs} from '@coreui/vue/src';
import BinaryOutputManager from '../../components/IqrfNet/BinaryOutputManager.vue';
import DaliManager from '../../components/IqrfNet/DaliManager.vue';
import LightManager from '../../components/IqrfNet/LightManager.vue';
import SensorManager from '../../components/IqrfNet/SensorManager.vue';
import StandardGrid from '../../components/IqrfNet/StandardGrid.vue';

@Component({
	components: {
		CTab,
		CTabs,
		BinaryOutputManager,
		DaliManager,
		LightManager,
		SensorManager,
		StandardGrid,
	},
	metaInfo: {
		title: 'iqrfnet.standard.title',
	}
})

/**
 * Standard manager page component
 */
export default class StandardManager extends Vue {}
</script>
