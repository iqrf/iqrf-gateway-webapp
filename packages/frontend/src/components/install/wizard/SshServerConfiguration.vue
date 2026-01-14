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
	<v-stepper-vertical-item
		:value='componentProps.index'
		:title='$t("components.install.wizard.sshServerConfiguration.title")'
	>
		<p class='mb-4'>
			{{ $t('components.install.wizard.sshServerConfiguration.text') }}
		</p>
		<v-form
			ref='form'
			v-model='formValidity'
		>
			<v-radio-group
				v-model='status'
			>
				<v-radio
					v-for='(option, i) of options'
					:key='i'
					:value='option.value'
					:label='option.text'
				/>
			</v-radio-group>
			<SshKeyTable
				v-if='isLoggedIn'
				:install='true'
			/>
		</v-form>
		<template #actions='{ next, prev }'>
			<IActionBtn
				:action='Action.Next'
				container-type='card'
				:loading='componentState === ComponentState.Action'
				:disabled='!formValidity || [ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				@click='onSubmit(next)'
			/>
			<IActionBtn
				:action='Action.Skip'
				class='ml-2'
				container-type='card'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Action].includes(componentState)'
				@click='next'
			/>
			<IActionBtn
				:action='Action.Previous'
				container-type='card'
				:disabled='[ComponentState.Loading, ComponentState.Reloading, ComponentState.Action].includes(componentState)'
				class='float-right'
				@click='prev'
			/>
		</template>
	</v-stepper-vertical-item>
</template>

<script lang='ts' setup>
import { type ServiceService } from '@iqrf/iqrf-gateway-webapp-client/services';
import {
	ErrorResponse,
	ServiceStatus,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { Action, ComponentState, IActionBtn } from '@iqrf/iqrf-vue-ui';
import { AxiosError } from 'axios';
import { storeToRefs } from 'pinia';
import {
	computed, onBeforeMount, ref, type Ref,
	type TemplateRef, useTemplateRef, watch,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { type VForm } from 'vuetify/components';

import SshKeyTable from '@/components/access-control/ssh-keys/SshKeyTable.vue';
import { ServiceAction } from '@/enums/controls';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

const componentProps = defineProps<{
	/// Step index
	index: number;
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const userStore = useUserStore();
const { isLoggedIn } = storeToRefs(userStore);
const i18n = useI18n();
const status: Ref<ServiceAction> = ref(ServiceAction.Disable);
const options = computed(() => [
	{
		value: ServiceAction.Enable,
		text: i18n.t('components.install.wizard.sshServerConfiguration.service.enable'),
	},
	{
		value: ServiceAction.Start,
		text: i18n.t('components.install.wizard.sshServerConfiguration.service.start'),
	},
	{
		value: ServiceAction.Disable,
		text: i18n.t('components.install.wizard.sshServerConfiguration.service.disable'),
	},
]);
const form: TemplateRef<VForm> = useTemplateRef('form');
const formValidity: Ref<boolean | null> = ref(null);
let service: ServiceService = useApiClient().getServiceService();

onBeforeMount(async () => {
	if (isLoggedIn.value) {
		await getConfig();
	}
});

watch(isLoggedIn, async (newVal: boolean, oldVal: boolean) => {
	if (newVal && !oldVal) {
		service = useApiClient().getServiceService();
		await getConfig();
	}
});

/**
 * Retrieves the SMTP configuration
 */
async function getConfig(): Promise<void> {
	componentState.value = componentState.value === ComponentState.Created ? ComponentState.Loading : ComponentState.Reloading;
	try {
		const response: ServiceStatus = await service.getStatus('ssh');
		if (response.enabled) {
			status.value = ServiceAction.Enable;
		} else {
			status.value = response.active ? ServiceAction.Start : ServiceAction.Disable;
		}
		componentState.value = ComponentState.Ready;
	} catch (error) {
		componentState.value = ComponentState.FetchFailed;
		if (error instanceof AxiosError) {
			const message = (error.response?.data as ErrorResponse | undefined)?.message ?? error.message;
			toast.error(i18n.t('components.install.wizard.sshServerConfiguration.messages.fetchFailed', { error: message }));
		}
	}
}

/**
 * Creates a new user
 * @param {Function} onClickNext Next button click handler
 */
async function onSubmit(onClickNext: Function): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	try {
		switch (status.value) {
			case ServiceAction.Enable:
				await service.enable('ssh');
				break;
			case ServiceAction.Start:
				await service.start('ssh');
				break;
			default:
				await service.disable('ssh');
				break;
		}
		componentState.value = ComponentState.Ready;
		onClickNext();
	} catch (error) {
		if (error instanceof AxiosError) {
			componentState.value = ComponentState.Ready;
			const message = (error.response?.data as ErrorResponse | undefined)?.message ?? error.message;
			toast.error(i18n.t('components.install.wizard.sshServerConfiguration.messages.saveFailed', { error: message }));
		}
	}
}
</script>
