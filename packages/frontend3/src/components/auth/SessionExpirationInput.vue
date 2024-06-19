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
		:label='$t("components.auth.expiration.label")'
		:prepend-inner-icon='mdiAccountClock'
	/>
</template>

<script lang='ts' setup>
import { UserSessionExpiration } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiAccountClock } from '@mdi/js';
import { computed, type PropType, type Ref } from 'vue';

import SelectInput from '@/components/layout/form/SelectInput.vue';
import i18n from '@/plugins/i18n';
import { type SelectItem } from '@/types/vuetify';

const modelValue = defineModel({
	type: [String, null] as PropType<string|null>,
	required: true,
});
const options: Ref<SelectItem[]> = computed((): SelectItem[] => {
	const expirations = [UserSessionExpiration.Default, UserSessionExpiration.Day, UserSessionExpiration.Week];
	return expirations.map((item: UserSessionExpiration): SelectItem => {
		return {
			title: i18n.global.t(`components.auth.expiration.expirations.${item}`).toString(),
			value: item,
		};
	});
});
</script>
