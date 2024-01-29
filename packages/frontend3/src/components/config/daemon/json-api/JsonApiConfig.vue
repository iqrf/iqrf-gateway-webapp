<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
	>
		<Card>
			<template #title>
				{{ $t('pages.configuration.daemon.json-api.title') }}
			</template>
			<template #titleActions>
				<v-tooltip
					location='bottom'
				>
					<template #activator='{ props }'>
						<v-btn
							v-bind='props'
							color='white'
							:icon='mdiReload'
							@click='getConfig'
						/>
					</template>
					{{ $t('common.buttons.reload') }}
				</v-tooltip>
			</template>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading, text'
			>
				<v-responsive>
					<section v-if='config'>
						<TextInput
							v-model='config.insId'
							:label='$t("components.configuration.daemon.json-api.instanceId")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.configuration.daemon.json-api.validation.instanceIdMissing")),
							]'
							required
						/>
						<v-checkbox
							v-model='config.validateJsonResponse'
							:label='$t("components.configuration.daemon.json-api.validateResponses")'
							density='compact'
							hide-details
						/>
					</section>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
					@click='onSubmit'
				>
					{{ $t('common.buttons.save') }}
				</v-btn>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	type IqrfGatewayDaemonComponent,
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonJsonSplitter,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiReload } from '@mdi/js';
import {
	onMounted,
	ref,
	type Ref,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import TextInput from '@/components/TextInput.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const form: Ref<typeof VForm | null> = ref(null);
let instance = '';
const config: Ref<IqrfGatewayDaemonJsonSplitter | null> = ref(null);

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	service.getComponent(IqrfGatewayDaemonComponentName.IqrfJsonSplitter)
		.then((response: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.IqrfJsonSplitter>): void => {
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
	const params = {...config.value};
	service.updateInstance(IqrfGatewayDaemonComponentName.IqrfJsonSplitter, instance, params)
		.then(() => {
			getConfig().then(() => {
				toast.success(
					i18n.t('components.configuration.daemon.json-api.messages.save.success'),
				);
			});
		})
		.catch(() => toast.error('TODO SAVE ERROR HANDLING'));
}

onMounted(() => {
	getConfig();
});
</script>