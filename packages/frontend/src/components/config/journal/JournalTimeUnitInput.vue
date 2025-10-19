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
		:label='$t("components.config.journal.timeUnit")'
		:items='options'
		:prepend-inner-icon='mdiClockTimeFour'
	/>
</template>

<script setup lang='ts'>
import { JournalTimeUnit } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { ISelectInput } from '@iqrf/iqrf-vue-ui';
import { mdiClockTimeFour } from '@mdi/js';
import { computed, PropType } from 'vue';
import { useI18n } from 'vue-i18n';

import { SelectItem } from '@/types/vuetify';

const modelValue = defineModel({
	type: String as PropType<JournalTimeUnit>,
	required: true,
});

const i18n = useI18n();
const options = computed((): SelectItem[] => [
	{
		title: i18n.t('components.config.journal.timeUnits.seconds'),
		value: JournalTimeUnit.Seconds,
	},
	{
		title: i18n.t('components.config.journal.timeUnits.minutes'),
		value: JournalTimeUnit.Minutes,
	},
	{
		title: i18n.t('components.config.journal.timeUnits.hours'),
		value: JournalTimeUnit.Hours,
	},
	{
		title: i18n.t('components.config.journal.timeUnits.days'),
		value: JournalTimeUnit.Days,
	},
	{
		title: i18n.t('components.config.journal.timeUnits.weeks'),
		value: JournalTimeUnit.Weeks,
	},
	{
		title: i18n.t('components.config.journal.timeUnits.months'),
		value: JournalTimeUnit.Months,
	},
	{
		title: i18n.t('components.config.journal.timeUnits.years'),
		value: JournalTimeUnit.Years,
	},
]);
</script>
