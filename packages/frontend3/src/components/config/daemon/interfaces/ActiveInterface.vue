<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
	>
		<Card>
			<template #title>
				{{ $t('components.configuration.daemon.interfaces.active.title') }}
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
				type='heading'
			>
				<v-responsive>
					<SelectInput
						v-model='active'
						:label='$t("components.configuration.daemon.interfaces.active.interface")'
						:items='activeOptions'
					/>
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
	type IqrfGatewayDaemonComponentConfiguration,
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonConfig,
	type IqrfGatewayDaemonComponentState,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiReload } from '@mdi/js';
import { onMounted, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import SelectInput from '@/components/SelectInput.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const form: Ref<typeof VForm | null> = ref(null);
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
	componentState.value = ComponentState.Loading;
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
