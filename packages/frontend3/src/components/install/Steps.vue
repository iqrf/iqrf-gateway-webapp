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
import { useInstallStore } from '@/store/install';
import { useDisplay } from 'vuetify/lib/framework.mjs';
import { storeToRefs } from 'pinia';

const display = useDisplay();
const installStore = useInstallStore();
const {getCurrentStep: currentStep, getSteps: steps} = storeToRefs(installStore);

</script>
