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
	<VueStepProgressIndicator
		:colors='colorConfig'
		:steps='progressSteps'
		:active-step='activeStep'
	/>
</template>

<script lang='ts'>
import Color from 'color';
import {Component, Vue} from 'vue-property-decorator';
import VueStepProgressIndicator from 'vue-step-progress-indicator';

@Component({
	components: {
		VueStepProgressIndicator,
	},
})

/**
 * Step progress for installation wizard
 */
export default class InstallWizardStepProgress extends Vue {

	/**
	 * @type {number} Step number
	 */
	private step = 0;

	/**
	 * @type {Array<string>} Array of install wizard steps
	 */
	private steps: Record<string, string> = {
		'introduction': '/install/',
		'webappUser': '/install/webapp-user/',
		'smtp': '/install/smtp/',
	};

	/**
	 * Vue step progress indicator color configuration
	 */
	private colorConfig = {
		progress__bubble: {
			active: {
				backgroundColor: '#367fa9',
				borderColor: '#367fa9',
				color: '#fff',
			},
			completed: {
				backgroundColor: InstallWizardStepProgress.getCompletedColor().hex(),
				borderColor: InstallWizardStepProgress.getCompletedColor().hex(),
				color: '#fff',
			},
			inactive: {
				backgroundColor: InstallWizardStepProgress.getInactiveColor().hex(),
				borderColor: InstallWizardStepProgress.getInactiveColor().hex(),
				color: '#fff'
			}
		},
		progress__bridge: {
			active: {
				backgroundColor: '#367fa9',
			},
			completed: {
				backgroundColor: InstallWizardStepProgress.getCompletedColor().hex(),
			},
			inactive: {
				backgroundColor: InstallWizardStepProgress.getInactiveColor().hex(),
			},
		},
		progress__label: {
			active: {
				color: '#367fa9',
			},
			completed: {
				color: InstallWizardStepProgress.getCompletedColor().hex(),
			},
			inactive: {
				color: InstallWizardStepProgress.getInactiveColor().hex(),
			},
		}
	};

	/**
	 * Returns the active step index
	 * @return {number} Index of the active step
	 */
	get activeStep(): number {
		return Object.values(this.steps).findIndex((url: string): boolean => (url === this.$route.path));
	}

	/**
	 * Returns list of steps for the progress indicator
	 * @return {Array<string>} List of steps
	 */
	get progressSteps(): Array<string> {
		return Object.keys(this.steps).map((item: string) => this.$t(`install.steps.${item}`).toString());
	}

	/**
	 * Builds steps for installation wizard
	 */
	created(): void {
		if (this.$store.getters['features/isEnabled']('gatewayPass')) {
			this.steps['gatewayUser'] = '/install/gateway-user/';
		}
		if (this.$store.getters['features/isEnabled']('ssh')) {
			this.steps['sshKey'] = '/install/ssh-keys/';
			this.steps['sshService'] = '/install/ssh-status/';
		}
	}

	/**
	 * Returns color for the completed step
	 * @private
	 * @return {Color} Color for the completed step
	 */
	private static getCompletedColor(): Color {
		const color: Color = Color('#367fa9');
		return color.lighten(0.4).desaturate(0.2);
	}

	/**
	 * Returns color for the inactive step
	 * @private
	 * @return {Color} Color for the inactive step
	 */
	private static getInactiveColor(): Color {
		return InstallWizardStepProgress.getCompletedColor().grayscale();
	}

	/**
	 * Advance the step progress indicator
	 */
	nextStep(): void {
		this.step++;
	}

}
</script>
