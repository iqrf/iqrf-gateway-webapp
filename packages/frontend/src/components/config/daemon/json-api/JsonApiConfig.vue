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
				{{ $t('pages.config.daemon.json-api.title') }}
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
				type='heading, text'
			>
				<v-responsive>
					<section v-if='config'>
						<ITextInput
							v-model='config.insId'
							:label='$t("components.config.daemon.json-api.instanceId")'
							:prepend-inner-icon='mdiIdentifier'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.config.daemon.json-api.validation.instanceIdMissing")),
							]'
							required
						/>
						<v-checkbox
							v-model='config.validateJsonResponse'
							:label='$t("components.config.daemon.json-api.validateResponses")'
						/>
					</section>
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
	type IqrfGatewayDaemonJsonSplitter,
	type IqrfGatewayDaemonJsonSplitterV3,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiIdentifier } from '@mdi/js';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const form: Ref<VForm | null> = ref(null);
let instance = '';
const config: Ref<IqrfGatewayDaemonJsonSplitter | IqrfGatewayDaemonJsonSplitterV3 | null> = ref(null);

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	try {
		config.value = (await service.getComponent(IqrfGatewayDaemonComponentName.IqrfJsonSplitter)).instances[0] ?? null;
		if (config.value !== null) {
			instance = config.value.instance;
			componentState.value = ComponentState.Ready;
		}
	} catch {
		toast.error('TODO FETCH ERROR HANDLING');
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...config.value };
	try {
		await service.updateInstance(IqrfGatewayDaemonComponentName.IqrfJsonSplitter, instance, params);
		await getConfig();
		toast.success(
			i18n.t('components.config.daemon.json-api.messages.save.success'),
		);
	} catch {
		toast.error('TODO SAVE ERROR HANDLING');
	}
}

onMounted(() => {
	getConfig();
});
</script>
