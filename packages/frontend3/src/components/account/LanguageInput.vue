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
	<SelectInput
		v-model='modelValue'
		:items='options'
		:label='$t("components.account.language.label")'
		:prepend-inner-icon='mdiTranslate'
	/>
</template>

<script lang='ts' setup>
import { mdiTranslate } from '@mdi/js';
import { defineModel, type PropType, type Ref } from 'vue';

import SelectInput from '@/components/SelectInput.vue';
import { getLanguageOptions } from '@/helpers/userData';
import { type SelectItem } from '@/types/vuetify';

const modelValue = defineModel({
	type: [String, null] as PropType<string|null>,
	required: true,
});
const options: Ref<SelectItem[]> = getLanguageOptions();
</script>
