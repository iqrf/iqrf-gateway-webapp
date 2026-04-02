<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

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
		:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
		@submit.prevent='saveConfig()'
	>
		<ICard>
			<template #title>
				{{ $t('pages.config.ws-proxy.title') }}
			</template>
			<template #titleActions>
				<IActionBtn
					:action='Action.Reload'
					container-type='card-title'
					:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					:disabled='componentState === ComponentState.Action'
					@click='getConfig()'
				/>
			</template>
			<v-alert
				v-if='componentState === ComponentState.FetchFailed'
				type='error'
				variant='tonal'
				:text='$t("components.config.ws-proxy.messages.fetch.failed")'
			/>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading@4'
			>
				<v-responsive>
					<section v-if='config'>
						<ITextInput
							v-model='config.address'
							:label='$t("components.config.ws-proxy.proxy")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.config.ws-proxy.validation.proxy.required")),
								(v: string) => ValidationRules.host(v, $t("components.config.ws-proxy.validation.proxy.hostname")),
							]'
							required
							:prepend-inner-icon='mdiArrowDecision'
						/>
						<INumberInput
							v-model='config.port'
							:label='$t("common.labels.port")'
							:rules='[
								(v: number | null) => ValidationRules.required(v, $t("common.validation.port.required")),
								(v: number) => ValidationRules.integer(v, $t("common.validation.port.integer")),
								(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.port.between")),
							]'
							:min='1'
							:max='65535'
							required
							:prepend-inner-icon='mdiNumeric'
						/>
						<ITextInput
							v-model='config.upstream'
							:label='$t("components.config.ws-proxy.upstream")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.config.ws-proxy.validation.upstream.required")),
								(v: string) => ValidationRules.url(v, $t("components.config.ws-proxy.validation.upstream.url"), /^wss?$/),
							]'
							required
							:prepend-inner-icon='mdiServer'
						>
							<template #append-inner>
								<WebsocketUrlForm
									:card-title='$t("components.config.ws-proxy.upstream")'
									:url='config.upstream'
									@edited='(upstream: string) => config!.upstream = upstream'
								/>
							</template>
						</ITextInput>
						<IPasswordInput
							v-model='config.token'
							:label='$t("components.config.ws-proxy.token")'
							:prepend-inner-icon='mdiKey'
						/>
					</section>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<IActionBtn
					:action='Action.Save'
					:loading='componentState === ComponentState.Action'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { WebSocketProxyService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { WebSocketProxyConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	INumberInput,
	IPasswordInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiArrowDecision, mdiKey, mdiNumeric, mdiServer } from '@mdi/js';
import { onMounted, ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import WebsocketUrlForm from '@/components/config/WebsocketUrlForm.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentState = ref<ComponentState>(ComponentState.Created);
const i18n = useI18n();
const service: WebSocketProxyService = useApiClient()
	.getConfigServices()
	.getWebSocketProxyService();
const form = useTemplateRef<VForm>('form');
const config = ref<WebSocketProxyConfig | null>(null);

async function getConfig(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		const cfg = await service.getConfig();
		if (!cfg.token) {
			cfg.token = '';
		}
		config.value = cfg;
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.ws-proxy.messages.fetch.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

async function saveConfig(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...config.value };
	if (params.token?.length === 0) {
		delete params.token;
	}
	try {
		await service.updateConfig(params);
		toast.success(
			i18n.t('components.config.ws-proxy.messages.save.success'),
		);
	} catch {
		toast.error(
			i18n.t('components.config.ws-proxy.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

onMounted(() => {
	getConfig();
});
</script>
