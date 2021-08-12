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
				backgroundColor: '#337AB7',
				borderColor: '#337AB7',
				color: '#fff',
			},
			completed: {
				backgroundColor: '#79a8d0',
				borderColor: '#79a8d0',
				color: '#fff',
			},
			inactive: {
				backgroundColor: '#a2a2a2',
				borderColor: '#a2a2a2',
				color: '#fff'
			}
		},
		progress__bridge: {
			active: {
				backgroundColor: '#337AB7',
			},
			completed: {
				backgroundColor: '#79a8d0',
			},
			inactive: {
				backgroundColor: '#a2a2a2',
			},
		},
		progress__label: {
			active: {
				color: '#337AB7',
			},
			completed: {
				color: '#79a8d0',
			},
			inactive: {
				color: '#a2a2a2',
			},
		}
	};

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
