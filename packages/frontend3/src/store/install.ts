import { InstallationCheckDependency, InstallationCheckPhpMissingExtensions } from '@iqrf/iqrf-gateway-webapp-client/types';
import { defineStore } from 'pinia';

import { useFeatureStore } from './features';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';

interface InstallState {
	checked: boolean;
	users: boolean;
	missingDependencies: InstallationCheckDependency[];
	missingExtensions: InstallationCheckPhpMissingExtensions | null;
	steps: InstallStep[];
}

interface InstallStep {
	index: number
	route: string
	title: string
}

export const useInstallStore = defineStore('install', {
	state: (): InstallState => ({
		checked: false,
		users: false,
		missingDependencies: [],
		missingExtensions: null,
		steps: [],
	}),
	actions: {
		setChecked(): void {
			this.checked = true;
		},
		setUsers(hasUsers: boolean): void {
			this.users = hasUsers;
		},
		setMissingDependencies(dependencies: InstallationCheckDependency[]): void {
			this.missingDependencies = dependencies;
		},
		setMissingExtensions(extensions: InstallationCheckPhpMissingExtensions): void {
			this.missingExtensions = extensions;
		},
		populateSteps(): void {
			const featureStore = useFeatureStore();
			const i18n = useI18n();
			const steps: InstallStep[] = [
				{
					index: 0,
					route: 'InstallDisambiguation',
					title: i18n.t('install.steps.introduction').toString(),
				},
				{
					index: 1,
					route: 'InstallUser',
					title: i18n.t('install.steps.webappUser').toString(),
				},
				{
					index: 2,
					route: 'InstallSmtp',
					title: i18n.t('install.steps.smtp').toString(),
				},
			];
			if (featureStore.isEnabled('gatewayPass')) {
				steps.push({
					index: steps.length,
					route: 'InstallGwUser',
					title: i18n.t('install.steps.gatewayUser').toString(),
				});
			}
			if (featureStore.isEnabled('ssh')) {
				steps.push(
					{
						index: steps.length,
						route: 'InstallSshKeys',
						title: i18n.t('install.steps.sshKeys').toString(),
					},
					{
						index: steps.length + 1,
						route: 'InstallSshService',
						title: i18n.t('install.steps.sshService').toString(),
					},
				);
			}
			this.steps = steps;
		}
	},
	getters: {
		isChecked(): boolean {
			return this.checked;
		},
		hasUsers(): boolean {
			return this.hasUsers;
		},
		getSteps(): InstallStep[] {
			return this.steps;
		},
		getCurrentStep(): InstallStep|null {
			const route = useRoute();
			const step = this.steps.find((item: InstallStep) => item.route === route.name);
			if (step === undefined) {
				return null;
			}
			return step;
		},
		getNextStep(): InstallStep|null {
			const step = this.getCurrentStep;
			if (step === null || step.index - 1 >= this.steps.length) {
				return null;
			}
			return this.steps[step.index + 1];
		}
	}
});
