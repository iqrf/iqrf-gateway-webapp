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
				{{ $t('pages.configuration.daemon.interfaces.cdc.title') }}
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
				type='heading@2'
			>
				<v-responsive>
					<section v-if='config'>
						<TextInput
							v-model='config.instance'
							:label='$t("components.configuration.daemon.instance")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.validation.instanceMissing")),
							]'
							required
						/>
						<TextInput
							v-model='config.IqrfInterface'
							:label='$t("components.configuration.daemon.interfaces.interface")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.interfaces.validation.interfaceMissing")),
							]'
							required
						/>
					</section>
				</v-responsive>
			</v-skeleton-loader>
			<span class='d-flex justify-space-around'>
				<v-menu
					v-model='showIntefaceMenu'
					location='top center'
					transition='slide-y-transition'
					:close-on-content-click='false'
					eager
				>
					<template #activator='{ props }'>
						<v-btn
							v-bind='props'
							color='primary'
						>
							{{ $t('components.configuration.daemon.interfaces.cdc.devices') }}
						</v-btn>
					</template>
					<InterfacePorts
						:interface-type='IqrfInterfaceType.CDC'
						@apply='applyInterface'
					/>
				</v-menu>
			</span>
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
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonCdc,
	type IqrfGatewayDaemonComponent,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { IqrfInterfaceType } from '@iqrf/iqrf-gateway-webapp-client/types/Iqrf';
import { onMounted, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import InterfacePorts from '@/components/config/daemon/interfaces/InterfacePorts.vue';
import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const form: Ref<VForm | null> = ref(null);
const config: Ref<IqrfGatewayDaemonCdc | null> = ref(null);
let instance = '';
const showIntefaceMenu: Ref<boolean> = ref(false);

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	service.getComponent(IqrfGatewayDaemonComponentName.IqrfCdc)
		.then((response: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.IqrfCdc>): void => {
			config.value = response.instances[0] ?? null;
			if (config.value !== null) {
				instance = config.value.instance;
				componentState.value = ComponentState.Ready;
			}
		})
		.catch(() => toast.error('TODO FETCH ERROR HANDLING'));
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const params = { ...config.value };
	service.updateInstance(IqrfGatewayDaemonComponentName.IqrfCdc, instance, params)
		.then(() => {
			getConfig().then(() => {
				toast.success(
					i18n.t('components.configuration.daemon.interfaces.cdc.messages.save.success'),
				);
			});
		})
		.catch(() => toast.error('TODO SAVE ERROR HANDLING'));
}

function applyInterface(iface: string): void {
	if (config.value === null) {
		return;
	}
	config.value.IqrfInterface = iface;
}

onMounted(() => {
	getConfig();
});
</script>
