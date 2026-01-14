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
	<ISelectInput
		v-model='modelValue'
		:items='options'
		:label='$t("components.iqrfnet.network-manager.rf-signal.timeLabel")'
		:prepend-inner-icon='mdiSendClock'
	/>
</template>

<script setup lang='ts'>
import { IqmeshTestRfMeasurementTime } from '@iqrf/iqrf-gateway-daemon-utils/enums/iqmesh';
import { ISelectInput } from '@iqrf/iqrf-vue-ui';
import { mdiSendClock } from '@mdi/js';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import { SelectItem } from '@/types/vuetify';

const modelValue = defineModel<IqmeshTestRfMeasurementTime>({
	required: true,
});

const i18n = useI18n();
const options = computed((): SelectItem[] => [
	{
		title: i18n.t('components.iqrfnet.network-manager.rf-signal.time', { time: IqmeshTestRfMeasurementTime.MS40 }),
		value: IqmeshTestRfMeasurementTime.MS40,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.rf-signal.time', { time: IqmeshTestRfMeasurementTime.MS360 }),
		value: IqmeshTestRfMeasurementTime.MS360,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.rf-signal.time', { time: IqmeshTestRfMeasurementTime.MS680 }),
		value: IqmeshTestRfMeasurementTime.MS680,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.rf-signal.time', { time: IqmeshTestRfMeasurementTime.MS1320 }),
		value: IqmeshTestRfMeasurementTime.MS1320,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.rf-signal.time', { time: IqmeshTestRfMeasurementTime.MS2600 }),
		value: IqmeshTestRfMeasurementTime.MS2600,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.rf-signal.time', { time: IqmeshTestRfMeasurementTime.MS5160 }),
		value: IqmeshTestRfMeasurementTime.MS5160,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.rf-signal.time', { time: IqmeshTestRfMeasurementTime.MS10280 }),
		value: IqmeshTestRfMeasurementTime.MS10280,
	},
	{
		title: i18n.t('components.iqrfnet.network-manager.rf-signal.time', { time: IqmeshTestRfMeasurementTime.MS20620 }),
		value: IqmeshTestRfMeasurementTime.MS20620,
	},
]);
</script>
