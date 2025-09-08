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
	<SelectInput
		v-if='options.length > 1 && modelValue !== null'
		v-model='modelValue'
		:items='options'
		:label='selectLabel'
		:prepend-inner-icon='mdiTranslate'
	/>
</template>

<script lang='ts' setup>
import { UserLanguage } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiTranslate } from '@mdi/js';
import { computed, ComputedRef, PropType } from 'vue';
import { useI18n } from 'vue-i18n';

import SelectInput from '@/components/layout/form/SelectInput.vue';
import { SelectItem } from '@/types/vuetify';

const i18n = useI18n();
const options: ComputedRef<SelectItem[]> = computed(() => {
	// const languages = Object.values(UserLanguage);
	const languages = [UserLanguage.English];
	return languages.map((item: UserLanguage): SelectItem => {
		return {
			title: i18n.t(`components.locale.languages.${item}`),
			value: item,
		};
	});
});
const componentProps = defineProps({
	label: {
		default: null,
		required: false,
		type: [String, null] as PropType<string | null>,
	},
});
const modelValue = defineModel({
	required: true,
	default: UserLanguage.English,
	type: [String, null] as PropType<UserLanguage | null>,
});
const selectLabel: ComputedRef<string> = computed(() => {
	return componentProps.label ?? i18n.t('components.accessControl.users.language');
});
</script>
