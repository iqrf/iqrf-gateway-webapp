<template>
	<VueStepProgressIndicator
		:colors='colorConfig'
		:steps='steps'
		:active-step='step'
	/>
</template>

<script lang='ts'>
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
	 * @var {number} step Step number
	 */
	private step = 0
	
	/**
	 * @var {Array<string>} steps Array of install wizard steps
	 */
	private steps: Array<string> = []

	/**
	 * Vue step progress indicator color configuration
	 */
	private colorConfig = {
		progress__bubble: {
			active: {
				backgroundColor: '#2eb85c',
				borderColor: '#2eb85c',
				color: '#fff',
			},
			completed: {
				backgroundColor: '#337ab7',
				borderColor: '#337ab7',
				color: '#fff',
			},
			inactive: {
				backgroundColor: '#ced2d8',
				borderColor: '#ced2d8',
				color: '#4f5d73'
			}
		},
		progress__label: {
			active: {
				color: '#e74c3c',
			},
			completed: {
				color: '#337ab7',
			},
			inactive: {
				color: '#4f5d73',
			},
		}
	}

	/**
	 * Builds steps for installation wizard
	 */
	created(): void {
		let steps = [
			'introduction',
			'webappUser'
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
	 * Advance the step progress indicator
	 */
	nextStep(): void {
		this.step++;
	}
}
</script>
