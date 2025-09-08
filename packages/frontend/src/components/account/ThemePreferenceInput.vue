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
	<v-radio-group
		v-model='modelValue'
		:label='$t("components.account.preferences.theme")'
		color='primary'
		density='compact'
	>
		<v-radio
			:label='$t("components.account.preferences.themes.auto")'
			:value='UserThemePreference.Auto'
		/>
		<v-radio
			:label='$t("components.account.preferences.themes.light")'
			:value='UserThemePreference.Light'
		/>
		<v-radio
			:label='$t("components.account.preferences.themes.dark")'
			:value='UserThemePreference.Dark'
		/>
	</v-radio-group>
</template>

<script setup lang='ts'>
import { UserThemePreference } from '@iqrf/iqrf-gateway-webapp-client/types';
import { type PropType } from 'vue';

/// Model value
const modelValue = defineModel({
	type: String as PropType<UserThemePreference>,
	required: true,
});
</script>
