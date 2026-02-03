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
	<v-form
		:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
		@submit.prevent='setStartupMode()'
	>
		<ICard>
			<template #title>
				{{ $t('components.gateway.mode.startup.title') }}
			</template>
			<template #titleActions>
				<IActionBtn
					:action='Action.Reload'
					container-type='card-title'
					:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					:disabled='componentState === ComponentState.Action'
					@click='getStartupMode()'
				/>
			</template>
			<v-alert
				v-if='componentState === ComponentState.FetchFailed'
				type='error'
				variant='tonal'
				:text='$t("components.gateway.mode.startup.noData.fetchError")'
			/>
			<v-skeleton-loader
				v-else
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading'
			>
				<v-responsive>
					<v-select
						v-model='startupMode'
						:items='startupModeOptions'
						:disabled='startupMode === null'
						hide-details
					/>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<IActionBtn
					:action='Action.Save'
					container-type='card'
					:loading='componentState === ComponentState.Action'
					:disabled='startupMode === null || [ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonIdeCounterpart,
	IqrfGatewayDaemonIdeCounterpartMode,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { Action, ComponentState, IActionBtn, ICard } from '@iqrf/iqrf-vue-ui';
import { computed, ComputedRef, onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';
import { SelectItem } from '@/types/vuetify';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const daemonConfigService: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();

const startupMode: Ref<IqrfGatewayDaemonIdeCounterpartMode | null> = ref(null);
const componentName = IqrfGatewayDaemonComponentName.IqrfIdeCounterpart;
const instance: Ref<IqrfGatewayDaemonIdeCounterpart | null> = ref(null);

const startupModeOptions: ComputedRef<SelectItem[]> = computed(() => {
	const modes = Object.values(IqrfGatewayDaemonIdeCounterpartMode);
	return modes.map((item: IqrfGatewayDaemonIdeCounterpartMode): SelectItem => {
		return {
			title: i18n.t(`components.gateway.mode.modes.${item}`),
			value: item,
		};
	});
});

async function getStartupMode(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		const rsp = await daemonConfigService.getComponent(componentName);
		const inst: IqrfGatewayDaemonIdeCounterpart = rsp.instances[0];
		if (inst.operMode === undefined) {
			inst.operMode = IqrfGatewayDaemonIdeCounterpartMode.Operational;
		}
		instance.value = inst;
		startupMode.value = inst.operMode;
		componentState.value = ComponentState.Idle;
	} catch {
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Idle;
		toast.error(i18n.t('components.gateway.mode.startup.messages.get.failed'));
	}
}

function setStartupMode(): void {
	if (instance.value === null || startupMode.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	const configuration: IqrfGatewayDaemonIdeCounterpart = {
		...instance.value,
		operMode: startupMode.value,
	};
	try {
		daemonConfigService.updateInstance(componentName, configuration.instance, configuration);
		toast.success(
			i18n.t(
				'components.gateway.mode.startup.messages.set.success',
				{ mode: i18n.t(`components.gateway.mode.modes.${startupMode.value}`) },
			),
		);
	} catch {
		toast.error(i18n.t('components.gateway.mode.startup.messages.set.failed'));
	}
	componentState.value = ComponentState.Idle;
}

onMounted(() => {
	getStartupMode();
});
</script>
