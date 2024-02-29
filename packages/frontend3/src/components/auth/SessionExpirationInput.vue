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
import { computed, defineModel, type PropType, type Ref } from 'vue';

import SelectInput from '@/components/SelectInput.vue';
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
