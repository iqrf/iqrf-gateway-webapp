<template>
	<v-stepper
		:alt-labels='$vuetify.breakpoint.smAndUp'
		:vertical='$vuetify.breakpoint.xsAndDown'
		:value='currentStep + 1'
		flat
	>
		<v-stepper-header>
			<v-stepper-step
				v-for='(item, i) in steps'
				:key='i'
				:step='i+1'
				:complete='currentStep > i'
			>
				{{ item.text }}
			</v-stepper-step>
		</v-stepper-header>
	</v-stepper>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

interface InstallWizardStep {
	id: string,
	to: string,
	text: string
}

/**
 * Step progress for installation wizard
 */
@Component
export default class InstallWizardStepProgress extends Vue {
	/**
	 * @var {Array<InstallWizardStep>} steps Wizard steps
	 */
	private steps: Array<InstallWizardStep> = [
		{
			id: 'introduction',
			to: '/install/',
			text: this.$t('install.steps.introduction').toString(),
		},
		{
			id: 'webappUser',
			to: '/install/webapp-user/',
			text: this.$t('install.steps.webappUser').toString(),
		},
		{
			id: 'smtp',
			to: '/install/smtp/',
			text: this.$t('install.steps.smtp').toString(),
		}
	];

	/**
	 * @var {number} currentStep Current step
	 */
	get currentStep(): number {
		return this.steps.findIndex((step: InstallWizardStep): boolean => {
			return step.to === this.$route.path;
		});
	}

	/**
	 * Builds steps for installation wizard
	 */
	created(): void {
		if (this.$store.getters['features/isEnabled']('gatewayPass')) {
			this.steps.push({
				id: 'gatewayUser',
				to: '/install/gateway-user/',
				text: this.$t('install.steps.gatewayUser').toString(),
			});
		}
		if (this.$store.getters['features/isEnabled']('ssh')) {
			this.steps.push(
				{
					id: 'sshKey',
					to: '/install/ssh-keys/',
					text: this.$t('install.steps.sshKey').toString(),
				},
				{
					id: 'sshService',
					to: '/install/ssh-status/',
					text: this.$t('install.steps.sshService').toString(),
				},
			);
		}
	}
}
</script>
