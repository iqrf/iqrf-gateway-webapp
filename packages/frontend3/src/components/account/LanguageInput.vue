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
