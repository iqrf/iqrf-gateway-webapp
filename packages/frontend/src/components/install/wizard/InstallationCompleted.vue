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
		:title='$t("components.install.wizard.installationCompleted.title")'
	>
		<p class='mb-4'>
			{{ $t('components.install.wizard.installationCompleted.text') }}
		</p>
		<template #actions='{ prev }'>
			<IActionBtn
				:icon='mdiCheck'
				container-type='card'
				:text='$t("components.install.wizard.installationCompleted.button")'
				@click='finishInstallation()'
			/>
			<IActionBtn
				:action='Action.Previous'
				class='float-right'
				container-type='card'
				@click='prev'
			/>
		</template>
	</v-stepper-vertical-item>
</template>

<script lang='ts' setup>
import { Action, IActionBtn } from '@iqrf/iqrf-vue-ui';
import { mdiCheck } from '@mdi/js';
import { useRouter } from 'vue-router';

import { useInstallStore } from '@/store/install';

const componentProps = defineProps<{
	/// Step index
	index: number;
}>();
const installStore = useInstallStore();
const router = useRouter();

/**
 * Finishes the installation
 */
async function finishInstallation(): Promise<void> {
	installStore.setRunning(false);
	installStore.resetStep();
	await router.push('/');
}

</script>
