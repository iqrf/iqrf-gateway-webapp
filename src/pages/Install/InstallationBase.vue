<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<TheWizard>
		<v-card v-if='!stepBlacklist.includes($route.path)' class='mb-5'>
			<v-card-text>
				<InstallWizardStepProgress ref='progress' class='progress-position' />
			</v-card-text>
		</v-card>
		<router-view @next-step='next' />
	</TheWizard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import InstallWizardStepProgress from '@/components/Install/InstallWizardStepProgress.vue';
import TheWizard from '@/components/TheWizard.vue';

@Component({
	components: {
		InstallWizardStepProgress,
		TheWizard,
	},
})

/**
 * Installation base page component
 */
export default class InstallationBase extends Vue {

	/**
	 * @constant {Array<string>} stepBlacklist
	 */
	private stepBlacklist: Array<string> = [
		'/install/gateway-info',
		'/install/restore',
		'/install/error/missing-dependency',
		'/install/error/missing-extension',
		'/install/error/missing-migration',
		'/install/error/sudo-error',
	];

	/**
	 * Advance the installation wizard
	 */
	private next(): void {
		(this.$refs.progress as InstallWizardStepProgress).nextStep();
	}
}
</script>

<style lang='scss' scoped>
.progress-position {
	justify-content: center !important;
	padding-left: 1rem;
	padding-right: 1rem;
}
</style>
