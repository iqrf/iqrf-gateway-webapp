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
				{{ $t('pages.config.iqrf-repository.title') }}
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
				:text='$t("components.config.iqrf-repository.messages.fetch.failed")'
			/>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading, text'
			>
				<v-responsive>
					<section v-if='config'>
						<ITextInput
							v-model='config.apiEndpoint'
							:label='$t("components.config.iqrf-repository.endpoint")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.config.iqrf-repository.validation.endpoint.required")),
								(v: string) => ValidationRules.url(v, $t("components.config.iqrf-repository.validation.endpoint.invalid"), /^https?$/),
							]'
							:prepend-inner-icon='mdiServer'
							required
						/>
						<v-checkbox
							v-model='useCredentials'
							:label='$t("components.config.iqrf-repository.credentials")'
							density='compact'
							:hide-details='!useCredentials'
						/>
						<ITextInput
							v-if='useCredentials'
							v-model='config.credentials.username'
							:label='$t("common.labels.username")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("common.validation.username.required")),
							]'
							:prepend-inner-icon='mdiAccount'
							required
						/>
						<IPasswordInput
							v-if='useCredentials'
							v-model='config.credentials.password'
							:label='$t("common.labels.password")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("common.validation.password.required")),
							]'
							:prepend-inner-icon='mdiKey'
							required
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
import {
	IqrfRepositoryConfig,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IPasswordInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiAccount, mdiKey, mdiServer } from '@mdi/js';
import {
	onMounted,
	ref,
	type Ref,
	type TemplateRef,
	toRaw,
	useTemplateRef,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { useRepositoryStore } from '@/store/repository';

const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service = useApiClient().getConfigServices().getIqrfRepositoryService();
const repositoryStore = useRepositoryStore();
const form: TemplateRef<VForm> = useTemplateRef('form');
const config: Ref<IqrfRepositoryConfig> = ref({
	apiEndpoint: '',
	credentials: {
		username: null,
		password: null,
	},
});
const useCredentials: Ref<boolean> = ref(false);

async function getConfig(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		config.value = await service.getConfig();
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.iqrf-repository.messages.fetch.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params: IqrfRepositoryConfig = structuredClone(toRaw(config.value));
	if (!useCredentials.value) {
		params.credentials = { username: null, password: null };
	}
	try {
		await service.updateConfig(params);
		toast.success(
			i18n.t('components.config.iqrf-repository.messages.save.success'),
		);
	} catch {
		toast.error(
			i18n.t('components.config.iqrf-repository.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

function storeConfig(cfg: IqrfRepositoryConfig): void {
	config.value = cfg;
	useCredentials.value = cfg.credentials.username !== null || cfg.credentials.password !== null;
}

onMounted(() => {
	const cfg = repositoryStore.configuration;
	if (cfg !== null) {
		storeConfig(cfg);
		componentState.value = ComponentState.Ready;
		return;
	}
	getConfig();
});
</script>
