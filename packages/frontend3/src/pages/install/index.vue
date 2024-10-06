<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<Head>
		<title>{{ $t('pages.install.wizard.title') }}</title>
	</Head>
	<Card>
		<template #title>
			{{ $t('pages.install.wizard.title') }}
		</template>
		{{ $t('pages.install.wizard.text') }}
		<v-stepper-vertical
			v-if='steps.length !== 0 && currentStep !== null'
			v-model='currentStep'
			flat
			@update:model-value='(step) => installStore.setCurrentStep(steps[step - 1])'
		>
			<AdminUserCreation
				v-if='steps.includes(InstallationStep.UserCreation)'
				:index='getIndex(InstallationStep.UserCreation)'
			/>
			<MailServerConfiguration
				v-if='steps.includes(InstallationStep.MailServerConfiguration)'
				:index='getIndex(InstallationStep.MailServerConfiguration)'
			/>
			<SshServerConfiguration
				v-if='steps.includes(InstallationStep.SshServerConfiguration)'
				:index='getIndex(InstallationStep.SshServerConfiguration)'
			/>
			<InstallationCompleted
				v-if='steps.includes(InstallationStep.InstallationCompleted)'
				:index='getIndex(InstallationStep.InstallationCompleted)'
			/>
		</v-stepper-vertical>
	</Card>
</template>

<route>
{
	"name": "InstallationWizard",
	"meta": {
		"layout": "Install",
		"requiresAuth": false,
		"installWizard": true,
	},
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';
import { storeToRefs } from 'pinia';
import { onMounted, type Ref, ref } from 'vue';

import AdminUserCreation
	from '@/components/install/wizard/AdminUserCreation.vue';
import InstallationCompleted
	from '@/components/install/wizard/InstallationCompleted.vue';
import MailServerConfiguration
	from '@/components/install/wizard/MailServerConfiguration.vue';
import SshServerConfiguration
	from '@/components/install/wizard/SshServerConfiguration.vue';
import Card from '@/components/layout/card/Card.vue';
import { useInstallStore } from '@/store/install';
import { InstallationStep } from '@/types/install';

/// Installation store
const installStore = useInstallStore();
/// The current installation wizard step
const currentStep: Ref<number | null> = ref(null);
/// Installation wizard steps
const steps: Ref<InstallationStep[]> = ref([]);

/**
 * Returns the index of the given step in the list of steps.
 * @param {InstallationStep} step Step to get the index for
 * @return {number} Index of the step
 */
function getIndex(step: InstallationStep): number {
	if (steps.value === null) {
		return 1;
	}
	return (steps.value ?? []).indexOf(step) + 1;
}

onMounted((): void => {
	const { getCurrentStep, getSteps } = storeToRefs(installStore);
	steps.value = getSteps.value ?? [];
	currentStep.value = (getCurrentStep.value === null) ? 1 : getIndex(getCurrentStep.value);
	installStore.setRunning(true);
});
</script>
