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
	<v-card v-if='currentStep !== null'>
		<v-stepper
			v-model='currentStep!.index'
			hide-actions
			:mobile='display.xs.value'
		>
			<v-stepper-header>
				<template
					v-for='(step, i) in steps'
					:key='i'
				>
					<v-divider v-if='i' />
					<v-stepper-item
						color='primary'
						:complete='currentStep !== null && currentStep.index > step.index'
						:title='$t(step.title)'
						:value='step.index'
					/>
				</template>
			</v-stepper-header>
		</v-stepper>
	</v-card>
</template>

<script lang='ts' setup>
import { storeToRefs } from 'pinia';
import { useDisplay } from 'vuetify';

import { useInstallStore } from '@/store/install';

const display = useDisplay();
const installStore = useInstallStore();
const { getCurrentStep: currentStep, getSteps: steps } = storeToRefs(installStore);

</script>
