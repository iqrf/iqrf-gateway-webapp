<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
	>
		<Card>
			<template #title>
				{{ $t('pages.configuration.daemon.interfaces.dpa.title') }}
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
							v-model.number='config.DpaHandlerTimeout'
							type='number'
							:label='$t("components.configuration.daemon.interfaces.dpa.timeout")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.interfaces.dpa.validation.timeoutMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.configuration.daemon.interfaces.dpa.validation.timeoutInvalid")),
								(v: number) => ValidationRules.min(v, 0, $t("components.configuration.daemon.interfaces.dpa.validation.timeoutInvalid"))
							]'
							required
						/>
					</section>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='componentState !== ComponentState.Ready || !isValid.value'
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
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonDpa,
	type IqrfGatewayDaemonComponent,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiReload } from '@mdi/js';
import { onMounted, type Ref, ref } from 'vue';
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
const config: Ref<IqrfGatewayDaemonDpa | null> = ref(null);
let instance = '';

async function getConfig(): Promise<void> {
	componentState.value = ComponentState.Loading;
	service.getComponent(IqrfGatewayDaemonComponentName.IqrfDpa)
		.then((response: IqrfGatewayDaemonComponent<IqrfGatewayDaemonComponentName.IqrfDpa>): void => {
			config.value = response.instances[0] ?? null;
			if (config.value !== null) {
				componentState.value = ComponentState.Ready;
				instance = config.value.instance;
			}
		})
		.catch(() => toast.error('TODO FETCH ERROR HANDLING'));
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	const params = {...config.value};
	service.updateInstance(IqrfGatewayDaemonComponentName.IqrfDpa, instance, params)
		.then(() => {
			getConfig().then(() => {
				toast.success(
					i18n.t('components.configuration.daemon.interfaces.dpa.messages.save.success'),
				);
			});
		})
		.catch(() => toast.error('TODO SAVE ERROR HANDLING'));
}

onMounted(() => {
	getConfig();
});
</script>
