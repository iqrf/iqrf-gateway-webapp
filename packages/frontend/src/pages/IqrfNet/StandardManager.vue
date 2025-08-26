<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
			</CTabs>
		</CCard>
		<SensorDataControls />
		<StandardDevices />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CTab, CTabs} from '@coreui/vue/src';
import BinaryOutputManager from '@/components/IqrfNet/StandardManager/BinaryOutputManager.vue';
import LightManager from '@/components/IqrfNet/StandardManager/LightManager.vue';
import SensorManager from '@/components/IqrfNet/StandardManager/SensorManager.vue';
import SensorDataControls from '@/components/IqrfNet/StandardManager/SensorDataControls.vue';
import StandardDevices from '@/components/IqrfNet/StandardManager/StandardDevices.vue';

@Component({
	components: {
		CTab,
		CTabs,
		BinaryOutputManager,
		LightManager,
		SensorManager,
		SensorDataControls,
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
