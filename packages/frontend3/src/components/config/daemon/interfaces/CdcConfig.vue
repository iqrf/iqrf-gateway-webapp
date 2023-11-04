<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
	>
		<Card>
			<template #title>
				{{ $t('pages.configuration.daemon.interfaces.cdc.title') }}
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
	type IqrfGatewayDaemonCdc,
	type IqrfGatewayDaemonComponent,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { IqrfInterfaceType } from '@iqrf/iqrf-gateway-webapp-client/types/Iqrf';
import { mdiReload } from '@mdi/js';
import { onMounted, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import InterfacePorts from '@/components/config/daemon/interfaces/InterfacePorts.vue';
import TextInput from '@/components/TextInput.vue';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const config: Ref<IqrfGatewayDaemonCdc | null> = ref(null);
let instance = '';
const showIntefaceMenu: Ref<boolean> = ref(false);

async function getConfig(): Promise<void> {
	componentState.value = ComponentState.Loading;
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
	if (config.value === null) {
		return;
	}
	const params = {...config.value};
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
