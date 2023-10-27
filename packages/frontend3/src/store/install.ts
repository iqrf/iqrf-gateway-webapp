import {
	Feature,
	type InstallationCheckDependency,
	type InstallationCheckPhpMissingExtensions,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { defineStore } from 'pinia';
import { useRoute } from 'vue-router';

import { useFeatureStore } from './features';

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
			const steps: InstallStep[] = [
				{
					index: 1,
					route: 'InstallDisambiguation',
					title: 'install.steps.introduction',
				},
				{
					index: 2,
					route: 'InstallUser',
					title: 'install.steps.webappUser',
				},
				{
					index: 3,
					route: 'InstallSmtp',
					title: 'install.steps.smtp',
				},
			];
			if (featureStore.isEnabled(Feature.gatewayPassword)) {
				steps.push({
					index: steps.length + 1,
					route: 'InstallGwUser',
					title: 'install.steps.gatewayUser',
				});
			}
			if (featureStore.isEnabled(Feature.ssh)) {
				steps.push(
					{
						index: steps.length + 1,
						route: 'InstallSshKeys',
						title: 'install.steps.sshKeys',
					},
					{
						index: steps.length + 2,
						route: 'InstallSshService',
						title: 'install.steps.sshService',
					},
				);
			}
			this.steps = steps;
		},
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
			if (route === undefined) {
				return null;
			}
			const step = this.steps.find((item: InstallStep): boolean => item.route === route.name);
			if (step === undefined) {
				return null;
			}
			return step;
		},
		getNextStep(): InstallStep|null {
			const step: InstallStep|null = this.getCurrentStep;
			if (step === null || step.index >= this.steps.length) {
				return null;
			}
			return this.steps[step.index];
		},
	},
});
