import { InstallationCheckDependency, InstallationCheckPhpMissingExtensions } from '@iqrf/iqrf-gateway-webapp-client/types';
import { defineStore } from 'pinia';

interface InstallState {
	checked: boolean;
	missingDependencies: InstallationCheckDependency[];
	missingExtensions: InstallationCheckPhpMissingExtensions | null;
}

export const useInstallStore = defineStore('install', {
	state: (): InstallState => ({
		checked: false,
		missingDependencies: [],
		missingExtensions: null,
	}),
	actions: {
		setChecked(): void {
			this.checked = true;
		},
		setMissingDependencies(dependencies: InstallationCheckDependency[]): void {
			this.missingDependencies = dependencies;
		},
		setMissingExtensions(extensions: InstallationCheckPhpMissingExtensions): void {
			this.missingExtensions = extensions;
		},
	},
	getters: {
		isChecked(): boolean {
			return this.checked;
		},
	}
});
