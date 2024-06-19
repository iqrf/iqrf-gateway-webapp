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
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
		@submit.prevent='onSubmit'
	>
		<Card>
			<template #title>
				{{ $t('components.configuration.daemon.interfaces.active.title') }}
			</template>
			<template #titleActions>
				<CardTitleActionBtn
					:action='Action.Reload'
					@click='getConfig'
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
						:label='$t("components.configuration.daemon.interfaces.active.interface")'
						:items='activeOptions'
						:prepend-inner-icon='mdiConnection'
					/>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<CardActionBtn
					:action='Action.Edit'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
					type='submit'
				/>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	type IqrfGatewayDaemonComponentConfiguration,
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonConfig,
	type IqrfGatewayDaemonComponentState,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiConnection } from '@mdi/js';
import { onMounted, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const form: Ref<VForm | null> = ref(null);
const active: Ref<IqrfGatewayDaemonComponentName | null> = ref(null);
const activeOptions = [
	{
		title: i18n.t('pages.configuration.daemon.interfaces.uart.title'),
		value: IqrfGatewayDaemonComponentName.IqrfUart,
	},
	{
		title: i18n.t('pages.configuration.daemon.interfaces.spi.title'),
		value: IqrfGatewayDaemonComponentName.IqrfSpi,
	},
	{
		title: i18n.t('pages.configuration.daemon.interfaces.cdc.title'),
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
	service.getConfig()
		.then((response: IqrfGatewayDaemonConfig) => {
			const ifaceComponents = response.components.filter((component: IqrfGatewayDaemonComponentConfiguration<IqrfGatewayDaemonComponentName>) => whitelist.includes(component.name));
			for (const component of ifaceComponents) {
				if (component.enabled) {
					active.value = component.name;
					break;
				}
			}
			componentState.value = ComponentState.Ready;
		})
		.catch(() => toast.error('TODO GET ERROR HANDLING'));
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || active.value === null) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const components: IqrfGatewayDaemonComponentState[] = whitelist.map((value: IqrfGatewayDaemonComponentName) => {
		return {
			enabled: value === active.value,
			name: value,
		};
	});
	service.changeEnabledComponents(components)
		.then(() => {
			getConfig().then(() => {
				toast.success(
					i18n.t('components.configuration.daemon.interfaces.active.messages.save.success'),
				);
			});
		})
		.catch(() => toast.error('TODO SAVE ERROR HANDLING'));
}

onMounted(() => {
	getConfig();
});
</script>
