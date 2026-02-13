<template>
	<ISelectInput
		v-model='modelValue'
		:items='options'
		:disabled='modelValue === DaemonMode.Unknown || modelValue === null'
		hide-details
	/>
</template>

<script setup lang='ts'>
import {
	DaemonMode,
} from '@iqrf/iqrf-gateway-daemon-utils/enums';
import {
	IqrfGatewayDaemonIdeCounterpartMode,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { ISelectInput } from '@iqrf/iqrf-vue-ui';
import { computed, type ComputedRef } from 'vue';
import { useI18n } from 'vue-i18n';

import { type SelectItem } from '@/types/vuetify';

/// IQRF Gateway Daemon mode input component model value
const modelValue = defineModel<DaemonMode | IqrfGatewayDaemonIdeCounterpartMode | null>({
	required: true,
});

const i18n = useI18n();

/// Available modes options
const options: ComputedRef<SelectItem[]> = computed((): SelectItem[] => [
	{
		title: i18n.t('components.gateway.mode.modes.operational'),
		value: DaemonMode.Operational,
	},
	{
		title: i18n.t('components.gateway.mode.modes.forwarding'),
		value: DaemonMode.Forwarding,
	},
	{
		title: i18n.t('components.gateway.mode.modes.service'),
		value: DaemonMode.Service,
	},
	...modelValue.value === DaemonMode.Unknown ? [
		{
			title: i18n.t('components.gateway.mode.modes.unknown'),
			value: DaemonMode.Unknown,
		},
	] : [],
]);
</script>


