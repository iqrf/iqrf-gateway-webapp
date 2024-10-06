/**
 * Copyright 2023-2024 IQRF Tech s.r.o.
 * Copyright 2023-2024 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

import { Feature, type InstallationChecks } from '@iqrf/iqrf-gateway-webapp-client/types';
import { defineStore } from 'pinia';

import { useApiClient } from '@/services/ApiClient';
import { useFeatureStore } from '@/store/features';
import { type InstallationErrors, InstallationStep } from '@/types/install';

/**
 * Installation store state
 */
interface InstallState {
	/// Is the installation checked?
	checked: boolean;
	/// Installation checks
	checks: InstallationChecks | null;
	/// Current installation step
	currentStep: InstallationStep | null;
	/// Is running?
	running: boolean;
}

export const useInstallStore = defineStore('install', {
	state: (): InstallState => ({
		checked: false,
		checks: null,
		currentStep: null,
		running: false,
	}),
	actions: {
		/**
		 * Checks the installation
		 */
		async check(): Promise<void> {
			this.checks = await useApiClient().getInstallationService().check();
		},
		/**
		 * Sets the installation as checked
		 */
		setChecked(): void {
			this.checked = true;
		},
		/**
		 * Sets if the installation has users.
		 * @param {boolean} hasUsers Has users?
		 */
		setHasUsers(hasUsers: boolean): void {
			if (this.checks === null) {
				return;
			}
			this.checks.hasUsers = hasUsers;

		},
		/**
		 * Sets the current installation step
		 * @param {InstallationStep} step The new installation step
		 */
		setCurrentStep(step: InstallationStep): void {
			this.currentStep = step;
		},
		/**
		 * Sets the installation wizard running state
		 * @param {boolean} isRunning Is the installation wizard running?
		 */
		setRunning(isRunning: boolean): void {
			if (!isRunning) {
				this.currentStep = null;
			}
			this.running = isRunning;
		},
	},
	getters: {
		/**
		 * Returns the installation checks
		 * @return {InstallationChecks | null} Installation checks
		 */
		getChecks(): InstallationChecks | null {
			return this.checks;
		},
		/**
		 * Returns the IQRF gateway ID
		 * @return {string | null} IQRF Gateway ID
		 */
		getGatewayId(): string | null {
			if (this.checks === null) {
				return null;
			}
			return this.checks.gwId;
		},
		/**
		 * Returns the installation errors
		 * @return {InstallationErrors | null} Installation errors
		 */
		getErrors(): InstallationErrors | null {
			if (this.checks === null) {
				return null;
			}
			return {
				missingDependencies: this.checks.dependencies.length !== 0,
				missingPhpExtensions: !this.checks.phpModules.allExtensionsLoaded,
				missingMigrations: !this.checks.allMigrationsExecuted,
				misconfiguredSudo: this.checks.sudo !== undefined && (!this.checks.sudo.exists || !this.checks.sudo.userSudo),
			} as InstallationErrors;
		},
		/**
		 * Checks if the installation has any errors
		 * @return {boolean} Is the installation without any errors?
		 */
		hasErrors(): boolean {
			if (this.checks === null) {
				return true;
			}
			return this.checks.dependencies.length !== 0 ||
				!this.checks.phpModules.allExtensionsLoaded ||
				!this.checks.allMigrationsExecuted;
		},
		/**
		 * Checks if the installation has no users
		 * @return {boolean} Is the installation without users?
		 */
		hasNoUsers(): boolean {
			return !(this.checks?.hasUsers ?? true);
		},
		/**
		 * Checks if the installation is checked
		 * @return {boolean} Is the installation checked?
		 */
		isChecked(): boolean {
			return this.checked;
		},
		/**
		 * Checks if the installation wizard is running
		 * @return {boolean} Is the installation wizard running?
		 */
		isRunning(): boolean {
			return this.hasNoUsers || this.running;
		},
		/**
		 * Returns available installation steps
		 * @return {InstallationStep[]} Available installation steps
		 */
		getSteps(): InstallationStep[] {
			const featureStore = useFeatureStore();
			const steps: InstallationStep[] = [];
			if (this.hasNoUsers) {
				steps.push(InstallationStep.UserCreation);
			}
			steps.push(...[
				InstallationStep.MailServerConfiguration,
			]);
			if (featureStore.isEnabled(Feature.ssh)) {
				steps.push(InstallationStep.SshServerConfiguration);
			}
			steps.push(InstallationStep.InstallationCompleted);
			return steps;
		},
		/**
		 * Returns the current installation step
		 * @return {InstallationStep|null} The current installation step
		 */
		getCurrentStep(): InstallationStep|null {
			if (this.checks === null) {
				return null;
			}
			const steps: InstallationStep[] = this.getSteps;
			if (this.currentStep === null || !steps.includes(this.currentStep)) {
				return steps[0] ?? null;
			}
			return this.currentStep;
		},
	},
	persist: {
		paths: ['currentStep', 'running'],
	},
});
