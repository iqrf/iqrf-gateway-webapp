<template>
	<VueStepProgressIndicator
		:colors='colorConfig'
		:steps='steps'
		:active-step='step'
	/>
</template>

<script lang='ts'>
import Color from 'color';
import {Component, Vue} from 'vue-property-decorator';
import VueStepProgressIndicator from 'vue-step-progress-indicator';

import ThemeManager from '../../helpers/themeManager';

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
	 * @var {number} step Step number
	 */
	private step = 0;

	/**
	 * @var {Array<string>} steps Array of install wizard steps
	 */
	private steps: Array<string> = [];

	/**
	 * Color for the active step
	 * @private
	 */
	private activeColor = ThemeManager.getPrimaryColor();

	/**
	 * Vue step progress indicator color configuration
	 */
	private colorConfig = {
		progress__bubble: {
			active: {
				backgroundColor: this.activeColor,
				borderColor: this.activeColor,
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
				backgroundColor: this.activeColor,
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
				color: this.activeColor,
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
	 * Builds steps for installation wizard
	 */
	created(): void {
		let steps = [
			'introduction',
			'webappUser',
			'smtp'
		];
		if (this.$store.getters['features/isEnabled']('gatewayPass')) {
			steps.push('gatewayUser');
		}
		if (this.$store.getters['features/isEnabled']('ssh')) {
			steps.push('sshKey', 'sshService');
		}
		this.steps = steps.map((item: string) => this.$t('install.steps.' + item).toString());
	}

	/**
	 * Returns color for the completed step
	 * @private
	 */
	private static getCompletedColor(): Color {
		let color: Color = Color(ThemeManager.getPrimaryColor());
		return color.lighten(0.4).desaturate(0.2);
	}

	/**
	 * Returns color for the inactive step
	 * @private
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
