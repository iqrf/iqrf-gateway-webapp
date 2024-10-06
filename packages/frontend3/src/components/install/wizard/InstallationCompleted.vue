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
	<v-stepper-vertical-item
		:value='componentProps.index'
		:title='$t("components.install.wizard.installationCompleted.title")'
	>
		<p class='mb-4'>
			{{ $t('components.install.wizard.installationCompleted.text') }}
		</p>
		<template #actions='{ prev }'>
			<CardActionBtn
				:icon='mdiCheck'
				:text='$t("components.install.wizard.installationCompleted.button")'
				@click='finishInstallation'
			/>
			<CardActionBtn
				:action='Action.Previous'
				class='float-right'
				@click='prev'
			/>
		</template>
	</v-stepper-vertical-item>
</template>

<script lang='ts' setup>
import { mdiCheck } from '@mdi/js';
import { useRouter } from 'vue-router';

import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import { useInstallStore } from '@/store/install';
import { Action } from '@/types/Action';

const componentProps = defineProps({
	index: {
		type: Number,
		required: true,
	},
});
const installStore = useInstallStore();
const router = useRouter();

/**
 * Finishes the installation
 */
async function finishInstallation(): Promise<void> {
	installStore.setRunning(false);
	await router.push('/');
}

</script>
