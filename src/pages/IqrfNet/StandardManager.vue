<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<div>
		<h1>{{ $t('iqrfnet.standard.title') }}</h1>
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
		<StandardDevices />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CTab, CTabs} from '@coreui/vue/src';
import BinaryOutputManager from '../../components/IqrfNet/BinaryOutputManager.vue';
import DaliManager from '../../components/IqrfNet/DaliManager.vue';
import LightManager from '../../components/IqrfNet/LightManager.vue';
import SensorManager from '../../components/IqrfNet/SensorManager.vue';
import StandardDevices from '../../components/IqrfNet/StandardDevices.vue';

@Component({
	components: {
		CTab,
		CTabs,
		BinaryOutputManager,
		DaliManager,
		LightManager,
		SensorManager,
		StandardDevices,
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
