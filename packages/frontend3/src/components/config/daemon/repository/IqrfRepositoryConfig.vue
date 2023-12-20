<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
	>
		<Card>
			<template #title>
				{{ $t('pages.configuration.daemon.repository.title') }}
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
							v-model='config.urlRepo'
							:label='$t("components.configuration.daemon.repository.url")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, "components.configuration.daemon.repository.validation.urlMissing"),
							]'
							required
						/>
						<v-checkbox
							v-model='updateCachePeriodically'
							:label='$t("components.configuration.daemon.repository.update")'
							density='compact'
							:hide-details='!updateCachePeriodically'
						/>
						<TextInput
							v-if='updateCachePeriodically'
							v-model.number='config.checkPeriodInMinutes'
							type='number'
							:label='$t("components.configuration.daemon.repository.updatePeriod")'
							:rules='updateCachePeriodically ? [
								(v: number|null) => ValidationRules.required(v, "components.configuration.daemon.repository.validation.updatePeriodMissing"),
								(v: number) => ValidationRules.integer(v, "components.configuration.daemon.repository.validation.updatePeriodInvalid"),
								(v: number) => ValidationRules.min(v, 0, "components.configuration.daemon.repository.validation.updatePeriodInvalid"),
							] : []'
							:required='updateCachePeriodically'
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
	type IqrfGatewayDaemonJsCache,
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
const config: Ref<IqrfGatewayDaemonJsCache | null> = ref(null);
const updateCachePeriodically: Ref<boolean> = ref(false);

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	service.getComponent(IqrfGatewayDaemonComponentName.IqrfJsCache)
		.then((response: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.IqrfJsCache>): void => {
			config.value = response.instances[0] ?? null;
			if (config.value !== null) {
				instance = config.value.instance;
				updateCachePeriodically.value = config.value.checkPeriodInMinutes !== 0;
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
	service.updateInstance(IqrfGatewayDaemonComponentName.IqrfJsCache, instance, params)
		.then(() => {
			getConfig().then(() => {
				toast.success(
					i18n.t('components.configuration.daemon.repository.messages.save.success'),
				);
			});
		})
		.catch(() => toast.error('TODO SAVE ERROR HANDLING'));
}

onMounted(() => {
	getConfig();
});
</script>
