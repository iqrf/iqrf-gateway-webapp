<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
	<div>
		<h1>{{ $t('config.daemon.components.title') }}</h1>
		<v-card>
			<v-card-text>
				<v-data-table
					:headers='headers'
					:items='components'
					:no-data-text='$t("table.messages.noRecords")'
				>
					<template #top>
						<v-toolbar dense flat>
							<v-spacer />
							<v-btn
								color='success'
								small
								to='/config/daemon/component/add'
							>
								<v-icon small>
									mdi-plus
								</v-icon>
								{{ $t('table.actions.add') }}
							</v-btn>
						</v-toolbar>
					</template>
					<template #[`item.enabled`]='{item}'>
						<v-menu>
							<template #activator='{on, attrs}'>
								<v-btn
									:color='item.enabled ? "success" : "error"'
									small
									v-bind='attrs'
									v-on='on'
								>
									{{ $t(`states.${item.enabled ? "enabled": "disabled"}`) }}
									<v-icon>mdi-menu-down</v-icon>
								</v-btn>
							</template>
							<v-list>
								<v-list-item @click='changeEnabled(item, true)'>
									{{ $t('states.enabled') }}
								</v-list-item>
								<v-list-item @click='changeEnabled(item, false)'>
									{{ $t('states.disabled') }}
								</v-list-item>
							</v-list>
						</v-menu>
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							color='info'
							small
							:to='"/config/daemon/component/edit/" + item.name'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn> <v-btn
							color='danger'
							small
							@click='component = item.name'
						>
							<v-icon small>
								mdi-delete
							</v-icon>
							{{ $t('table.actions.delete') }}
						</v-btn>
						<v-dialog
							v-model='deleteDialog'
							width='50%'
							persistent
							no-click-animation
						>
							<v-card>
								<v-card-title class='text-h5 error'>
									<v-icon>mdi-alert</v-icon>
									{{ $t('config.daemon.components.messages.deleteTitle') }}
								</v-card-title>
								<v-card-text>
									{{ $t('config.daemon.components.messages.deletePrompt', {component: component}) }}
								</v-card-text>
								<v-card-actions>
									<v-spacer />
									<v-btn
										@click='component = ""'
									>
										{{ $t('forms.no') }}
									</v-btn>
									<v-btn
										color='error'
										@click='removeComponent'
									>
										{{ $t('forms.yes') }}
									</v-btn>
								</v-card-actions>
							</v-card>
						</v-dialog>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {UserRole} from '@/services/AuthenticationService';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IComponent} from '@/interfaces/daemonComponent';
import {MetaInfo} from 'vue-meta';

@Component({
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as ComponentList).pageTitle
		};
	},
})

/**
 * Component list card for Daemon component configuration
 */
export default class ComponentList extends Vue {
	/**
	 * @var {string} component Name of daemon component used for remove modal
	 */
	private component = '';

	/**
	 * @var {Array<IComponent>} components Array of Daemon component objects
	 */
	private components: Array<IComponent> = [];

	/**
	 * @var {UserRole} role User role
	 */
	private role: UserRole = UserRole.NORMAL;

	/**
	 * @constant {Array<DataTableHeader>} headers Array of CoreUI data table columns
	 */
	private headers: Array<DataTableHeader> = [
		{
			value: 'name',
			text: this.$t('config.daemon.components.form.name').toString(),
		},
		{
			value: 'startlevel',
			text: this.$t('config.daemon.components.form.startLevel').toString(),
		},
		{
			value: 'libraryPath',
			text: this.$t('config.daemon.components.form.libraryPath').toString(),
		},
		{
			value: 'libraryName',
			text: this.$t('config.daemon.components.form.libraryName').toString(),
		},
		{
			value: 'enabled',
			text: this.$t('states.enabled').toString(),
			filterable: false,
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			filterable: false,
			sortable: false,
		}
	];

	/**
	 * @var {boolean} deleteDialog Delete dialog visibility
	 */
	get deleteDialog(): boolean {
		return this.component !== '';
	}

	/**
	 * Computes page title depending on the user role
	 * @returns {string} Page title string
	 */
	get pageTitle(): string {
		return this.role === UserRole.ADMIN ?
			this.$t('config.daemon.components.title').toString() : this.$t('config.selectedComponents.title').toString();
	}

	/**
	 * Retrieves user role
	 */
	created(): void {
		this.role = this.$store.getters['user/getRole'];
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		const titleEl = document.getElementById('title');
		if (titleEl !== null) {
			titleEl.innerText = this.pageTitle;
		}
		this.getConfig();
	}

	/**
	 * Retrieves list of Daemon components
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private getConfig(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return DaemonConfigurationService.getComponent('')
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				if (this.role === UserRole.ADMIN) {
					this.components = response.data.components;
				} else {
					const whitelistedComponents = ['iqrf::IqrfCdc', 'iqrf::IqrfSpi', 'iqrf::IqrfUart'];
					this.components = response.data.components.filter((component: IComponent) => {
						if (whitelistedComponents.includes(component.name)) {
							return component;
						}
					});
				}
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.components.messages.listFailed');
			});
	}

	/**
	 * Enables or disables Daemon component
	 * @param {IComponent} component Component to be updated
	 * @param {boolean} enabled New state of component
	 */
	private changeEnabled(component: IComponent, enabled: boolean): void {
		if (component.enabled !== enabled) {
			if (!this.canEnable(component.name)) {
				this.$toast.info(
					this.$t('config.daemon.components.messages.multipleInterfaces').toString()
				);
				return;
			}
			const conf = {
				...component
			};
			conf.enabled = enabled;
			DaemonConfigurationService.updateComponent(component.name, conf)
				.then(() => {
					this.getConfig().then(() => {
						this.$toast.success(
							this.$t('config.daemon.components.messages.changeEnabledSuccess', {component: component.name}).toString()
						);
					});
				})
				.catch((error: AxiosError) => {
					extendedErrorToast(error, 'config.daemon.components.messages.changeEnabledFailed', {component: component.name});
				});
		}
	}

	/**
	 * Checks if an IQRF interface can be enabled, depending on the state of ther interfaces
	 * @param {string} enabledComponent Component to be enabled
	 * @returns {boolean} True if component can be enabled
	 */
	private canEnable(enabledComponent: string): boolean|void {
		if (this.components === null) {
			return;
		}
		const whitelist = ['iqrf::IqrfCdc', 'iqrf::IqrfSpi', 'iqrf::IqrfUart'];
		if (!whitelist.includes(enabledComponent)) {
			return true;
		}
		let enabled = 0;
		this.components.forEach((component: IComponent) => {
			if (whitelist.includes(component.name) && component.enabled && component.name !== enabledComponent) {
				enabled++;
			}
		});
		return enabled <= 0;
	}


	/**
	 * Removes a Daemon component configuration
	 */
	private removeComponent(): void {
		this.$store.commit('spinner/SHOW');
		const component = this.component;
		this.component = '';
		DaemonConfigurationService.deleteComponent(component)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(this.$t('config.daemon.components.messages.deleteSuccess', {component: component}).toString());
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.components.messages.deleteFailed', {component: component});
			});
	}

}
</script>
