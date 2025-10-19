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
				{{ $t('pages.config.daemon.db.title') }}
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
				:text='$t("components.config.daemon.db.messages.fetch.failed")'
			/>
			<v-skeleton-loader
				:loading='componentState === ComponentState.Loading'
				type='list-item@3'
			>
				<v-responsive v-if='config'>
					<v-checkbox
						v-model='config.autoEnumerateBeforeInvoked'
						:label='$t("components.config.daemon.db.autoEnumerate")'
						density='compact'
						hide-details
					/>
					<v-checkbox
						v-model='config.enumerateOnLaunch'
						:label='$t("components.config.daemon.db.enumerateOnLaunch")'
						density='compact'
						hide-details
					/>
					<v-checkbox
						v-model='config.metadataToMessages'
						:label='$t("components.config.daemon.db.includeMetadata")'
						density='compact'
						hide-details
					/>
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
	type IqrfGatewayDaemonService,
} from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonDb,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
} from '@iqrf/iqrf-vue-ui';
import { onMounted, ref, type Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayDaemonService = useApiClient()
	.getConfigServices()
	.getIqrfGatewayDaemonService();
const form: Ref<VForm | null> = useTemplateRef('form');
const config: Ref<IqrfGatewayDaemonDb | null> = ref(null);
let instance = '';

async function getConfig(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		const data = await service.getComponent(IqrfGatewayDaemonComponentName.IqrfDb);
		config.value = data.instances[0] ?? null;
		if (config.value === null) {
			throw new Error('Configuration instance missing.');
		}
		instance = config.value.instance;
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.daemon.db.messages.fetch.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...config.value };
	try {
		await service.updateInstance(IqrfGatewayDaemonComponentName.IqrfDb, instance, params);
		toast.success(
			i18n.t('components.config.daemon.db.messages.save.success'),
		);
	} catch {
		toast.error(
			i18n.t('components.config.daemon.db.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

onMounted(() => {
	getConfig();
});
</script>
