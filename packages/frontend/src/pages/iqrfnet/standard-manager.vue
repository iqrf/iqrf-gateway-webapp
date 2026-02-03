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
		<Head>
			<title>{{ $t('pages.iqrfnet.standard-manager.title') }}</title>
		</Head>
		<v-toolbar
			density='compact'
			class='rounded-t'
			color='primary'
		>
			<v-tabs v-model='tab'>
				<v-tab
					class='toolbar-tab-title'
					value='0'
					:text='$t("components.iqrfnet.standard-manager.sensor.title")'
				/>
				<v-tab
					class='toolbar-tab-title'
					value='1'
					:text='$t("components.iqrfnet.standard-manager.binary-output.title")'
				/>
				<v-tab
					class='toolbar-tab-title'
					value='2'
					:text='$t("components.iqrfnet.standard-manager.light.title")'
				/>
			</v-tabs>
		</v-toolbar>
		<v-tabs-window v-model='tab'>
			<v-tabs-window-item
				value='0'
			>
				<SensorManager />
			</v-tabs-window-item>
			<v-tabs-window-item
				value='1'
			>
				<BinaryOutputManager />
			</v-tabs-window-item>
			<v-tabs-window-item
				value='2'
			>
				<LightManager />
			</v-tabs-window-item>
		</v-tabs-window>
		<SensorDataControls class='my-4' />
		<StandardDevices />
	</div>
</template>

<route>
{
	"name": "StandardManager",
	"meta": {
		"requiresProxy": true,
	},
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';
import { ref, type Ref } from 'vue';

import BinaryOutputManager from '@/components/iqrfnet/standard-manager/BinaryOutputManager.vue';
import LightManager from '@/components/iqrfnet/standard-manager/LightManager.vue';
import SensorDataControls from '@/components/iqrfnet/standard-manager/SensorDataControls.vue';
import SensorManager from '@/components/iqrfnet/standard-manager/SensorManager.vue';
import StandardDevices from '@/components/iqrfnet/standard-manager/StandardDevices.vue';

const tab: Ref<number> = ref(0);

</script>
