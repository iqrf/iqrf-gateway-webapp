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
	<IModalWindow
		v-model='show'
	>
		<template #activator='{ props }'>
			<IActionBtn
				:action='Action.Custom'
				color='warning'
				:icon='mdiReload'
				:text='$t("components.gateway.power.reboot.action")'
				v-bind='props'
			/>
		</template>
		<ICard header-color='warning'>
			<template #title>
				{{ $t('components.gateway.power.reboot.title') }}
			</template>
			{{ $t('components.gateway.power.reboot.prompt') }}
			<template #actions>
				<IActionBtn
					color='warning'
					:icon='mdiReload'
					:loading='componentState === ComponentState.Action'
					:text='$t("components.gateway.power.reboot.action")'
					@click='reboot()'
				/>
				<v-spacer />
				<IActionBtn
					:action='Action.Cancel'
					:disabled='componentState === ComponentState.Action'
					@click='close()'
				/>
			</template>
		</ICard>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { PowerService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { PowerActionResponse } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { Action, ComponentState, IActionBtn, ICard, IModalWindow } from '@iqrf/iqrf-vue-ui';
import { mdiReload } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';

/// Dialog visibility
const show: Ref<boolean> = ref(false);
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
/// Internalization instance
const i18n = useI18n();
/// Power service
const service: PowerService = useApiClient().getGatewayServices().getPowerService();

/**
 * Reboots the gateway
 */
async function reboot(): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		const response: PowerActionResponse = await service.reboot();
		close();
		toast.success(
			i18n.t('components.gateway.power.reboot.messages.success', { time: i18n.d(response.timestamp.toJSDate(), 'time') }),
		);
	} catch {
		toast.error(i18n.t('components.gateway.power.reboot.messages.failed'));
	}
	componentState.value = ComponentState.Idle;
}
/**
 * Closes the dialog window
 */
function close(): void {
	show.value = false;
}
</script>
