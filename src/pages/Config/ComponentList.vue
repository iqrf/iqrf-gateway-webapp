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
		<h1>
			{{ $t('config.daemon.components.title') }}
		</h1>
		<CCard>
			<CCardHeader>
				<CButton
					color='success'
					size='sm'
					class='float-right'
					to='/config/daemon/component/add'
				>
					<CIcon :content='cilPlus' size='sm' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:fields='fields'
					:items='components'
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
					:striped='true'
					:sorter='{ external: false, resetable: true }'
				>
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #enabled='{item}'>
						<td>
							<CDropdown
								:color='item.enabled ? "success" : "danger"'
								:toggler-text='$t(`states.${item.enabled ? "enabled": "disabled"}`)'
								size='sm'
							>
								<CDropdownItem @click='changeEnabled(item, true)'>
									{{ $t('states.enabled') }}
								</CDropdownItem>
								<CDropdownItem @click='changeEnabled(item, false)'>
									{{ $t('states.disabled') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='info'
								size='sm'
								:to='"/config/daemon/component/edit/" + item.name'
							>
								<CIcon :content='cilPencil' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								color='danger'
								size='sm'
								@click='component = item.name'
							>
								<CIcon :content='cilTrash' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			color='danger'
			:show='component !== ""'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.daemon.components.messages.deleteTitle') }}
				</h5>
			</template>
			{{ $t('config.daemon.components.messages.deletePrompt', {component: component}) }}
			<template #footer>
				<CButton color='danger' @click='component = ""'>
					{{ $t('forms.no') }}
				</CButton>
				<CButton color='success' @click='removeComponent'>
					{{ $t('forms.yes') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CDropdown, CDropdownItem, CIcon, CModal} from '@coreui/vue/src';

import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';
import {UserRole} from '@/services/AuthenticationService';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IComponent} from '@/interfaces/Config/Daemon';
import {IField} from '@/interfaces/Coreui';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CDropdown,
		CDropdownItem,
		CIcon,
		CModal,
	},
	data: () => ({
		cilPencil,
		cilPlus,
		cilTrash,
	}),
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
	 * @constant {Array<IField>} fields Array of CoreUI data table columns
	 */
	private fields: Array<IField> = [
		{
			key: 'name',
			label: this.$t('config.daemon.components.form.name'),
		},
		{
			key: 'startlevel',
			label: this.$t('config.daemon.components.form.startLevel'),
		},
		{
			key: 'libraryPath',
			label: this.$t('config.daemon.components.form.libraryPath'),
		},
		{
			key: 'libraryName',
			label: this.$t('config.daemon.components.form.libraryName'),
		},
		{
			key: 'enabled',
			label: this.$t('states.enabled'),
			filter: false,
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			filter: false,
			sorter: false,
		}
	];

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
