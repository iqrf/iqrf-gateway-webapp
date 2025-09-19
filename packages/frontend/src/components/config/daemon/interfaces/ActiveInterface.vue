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
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
		@submit.prevent='onSubmit()'
	>
		<ICard>
			<template #title>
				{{ $t('components.config.daemon.interfaces.active.title') }}
			</template>
			<template #titleActions>
				<IActionBtn
					:action='Action.Reload'
					container-type='card-title'
					@click='getConfig()'
				/>
			</template>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading'
			>
				<v-responsive>
					<SelectInput
						v-model='active'
						:label='$t("components.config.daemon.interfaces.active.interface")'
						:items='activeOptions'
						:prepend-inner-icon='mdiConnection'
					/>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<IActionBtn
					:action='Action.Edit'
					container-type='card'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading, ComponentState.Action].includes(componentState)'
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
	type IqrfGatewayDaemonComponentState,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
} from '@iqrf/iqrf-vue-ui';
import { mdiConnection } from '@mdi/js';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import SelectInput from '@/components/layout/form/SelectInput.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const form: Ref<VForm | null> = ref(null);
const active: Ref<IqrfGatewayDaemonComponentName | null> = ref(null);
const activeOptions = [
	{
		title: i18n.t('pages.config.daemon.interfaces.uart.title'),
		value: IqrfGatewayDaemonComponentName.IqrfUart,
	},
	{
		title: i18n.t('pages.config.daemon.interfaces.spi.title'),
		value: IqrfGatewayDaemonComponentName.IqrfSpi,
	},
	{
		title: i18n.t('pages.config.daemon.interfaces.cdc.title'),
		value: IqrfGatewayDaemonComponentName.IqrfCdc,
	},
];
const whitelist = [
	IqrfGatewayDaemonComponentName.IqrfCdc,
	IqrfGatewayDaemonComponentName.IqrfSpi,
	IqrfGatewayDaemonComponentName.IqrfUart,
];

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	try {
		const ifaceComponents = (await service.getConfig()).components.filter(component => whitelist.includes(component.name));
		for (const component of ifaceComponents) {
			if (component.enabled) {
				active.value = component.name;
				break;
			}
		}
	} catch {
		toast.error('TODO GET ERROR HANDLING');
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || active.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	const components: IqrfGatewayDaemonComponentState[] = whitelist.map((value: IqrfGatewayDaemonComponentName) => {
		return {
			enabled: value === active.value,
			name: value,
		};
	});
	try {
		await service.updateEnabledComponents(components);
		await getConfig();
		toast.success(
			i18n.t('components.config.daemon.interfaces.active.messages.save.success'),
		);
	} catch {
		toast.error('TODO SAVE ERROR HANDLING');
	}
}

onMounted(() => {
	getConfig();
});
</script>
