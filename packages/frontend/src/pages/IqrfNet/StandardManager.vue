<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		<v-card class='mb-5'>
			<v-tabs v-model='activeTab' :show-arrows='true'>
				<v-tab>{{ $t('iqrfnet.standard.sensor.title') }}</v-tab>
				<v-tab>{{ $t('iqrfnet.standard.binaryOutput.title') }}</v-tab>
				<v-tab>{{ $t('iqrfnet.standard.light.title') }}</v-tab>
				<v-tab>{{ $t('iqrfnet.standard.dali.title') }}</v-tab>
			</v-tabs>
			<v-tabs-items v-model='activeTab'>
				<v-tab-item :transition='false'>
					<SensorManager />
				</v-tab-item>
				<v-tab-item :transition='false'>
					<BinaryOutputManager />
				</v-tab-item>
				<v-tab-item :transition='false'>
					<LightManager />
				</v-tab-item>
				<v-tab-item :transition='false'>
					<DaliManager />
				</v-tab-item>
			</v-tabs-items>
		</v-card>
		<StandardDevices />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import BinaryOutputManager from '@/components/IqrfNet/StandardManager/BinaryOutputManager.vue';
import DaliManager from '@/components/IqrfNet/StandardManager/DaliManager.vue';
import LightManager from '@/components/IqrfNet/StandardManager/LightManager.vue';
import SensorManager from '@/components/IqrfNet/StandardManager/SensorManager.vue';
import StandardDevices from '@/components/IqrfNet/StandardManager/StandardDevices.vue';

@Component({
	components: {
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
export default class StandardManager extends Vue {

	/**
	 * @const {number} activeTab Default active tab
	 */
	private activeTab = 0;
}
</script>
