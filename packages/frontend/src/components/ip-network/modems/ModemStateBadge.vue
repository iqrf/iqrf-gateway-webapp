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
	<v-chip
		:color
		label
		size='small'
	>
		{{ text }}
	</v-chip>
</template>

<script setup lang='ts'>
import { ModemState } from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import { computed, type ComputedRef } from 'vue';
import { useI18n } from 'vue-i18n';

/// Component props
const componentProps = defineProps<{
	state: ModemState;
}>();
const i18n = useI18n();

/// Badge color
const color = computed(() => {
	switch (componentProps.state) {
		case ModemState.failed:
			return 'error';
		case ModemState.locked:
		case ModemState.unknown:
			return 'warning';
		case ModemState.connected:
			return 'success';
		case ModemState.registered:
			return 'info';
		default:
			return 'secondary';
	}
});
/// Badge text
const text: ComputedRef<string> = computed((): string => {
	const data: Record<ModemState, string> = {
		[ModemState.connected]: i18n.t('components.ipNetwork.modems.columns.states.connected'),
		[ModemState.connecting]: i18n.t('components.ipNetwork.modems.columns.states.connecting'),
		[ModemState.disabled]: i18n.t('components.ipNetwork.modems.columns.states.disabled'),
		[ModemState.disabling]: i18n.t('components.ipNetwork.modems.columns.states.disabling'),
		[ModemState.disconnecting]: i18n.t('components.ipNetwork.modems.columns.states.disconnecting'),
		[ModemState.enabled]: i18n.t('components.ipNetwork.modems.columns.states.enabled'),
		[ModemState.enabling]: i18n.t('components.ipNetwork.modems.columns.states.enabling'),
		[ModemState.failed]: i18n.t('components.ipNetwork.modems.columns.states.failed'),
		[ModemState.initializing]: i18n.t('components.ipNetwork.modems.columns.states.initializing'),
		[ModemState.locked]: i18n.t('components.ipNetwork.modems.columns.states.locked'),
		[ModemState.registered]: i18n.t('components.ipNetwork.modems.columns.states.registered'),
		[ModemState.searching]: i18n.t('components.ipNetwork.modems.columns.states.searching'),
		[ModemState.unknown]: i18n.t('components.ipNetwork.modems.columns.states.unknown'),
	};
	return data[componentProps.state] ?? '';
});
</script>
