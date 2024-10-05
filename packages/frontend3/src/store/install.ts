/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

import {
	Feature,
	type InstallationChecks,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { defineStore } from 'pinia';

import router from '@/router';
import { useApiClient } from '@/services/ApiClient';
import { useFeatureStore } from '@/store/features';
import { type InstallationErrors } from '@/types/install';

/**
 * Installation store state
 */
interface InstallState {
	/// Is the installation checked?
	checked: boolean;
	/// Installation checks
	checks: InstallationChecks | null;
	steps: InstallStep[];
}

/**
 * Installation step
 */
interface InstallStep {
	/// Step index
	index: number
	/// Step route
	route: string
	/// Step title
	title: string
}

export const useInstallStore = defineStore('install', {
	state: (): InstallState => ({
		checked: false,
		checks: null,
		steps: [],
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
		getSteps(): InstallStep[] {
			return this.steps;
		},
		getCurrentStep(): InstallStep|null {
			const route = router.currentRoute.value;
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
