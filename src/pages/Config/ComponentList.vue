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
					<CIcon :content='icons.add' size='sm' />
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
								:toggler-text='$t("states." + (item.enabled ? "enabled": "disabled"))'
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
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								color='danger'
								size='sm'
								@click='component = item.name'
							>
								<CIcon :content='icons.remove' size='sm' />
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
					{{ $t('config.components.messages.deleteTitle') }}
				</h5>
			</template>
			{{ $t('config.components.messages.deletePrompt', {component: component}) }}
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
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import { MetaInfo } from 'vue-meta';
import { IField } from '../../interfaces/coreui';
import { AxiosError } from 'axios';
import { Dictionary } from 'vue-router/types/router';
import {IComponent} from '../../interfaces/daemonComponent';

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
	private component = ''

	/**
	 * @var {Array<IComponent>} components Array of Daemon component objects
	 */
	private components: Array<IComponent> = []

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
	]

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI Icons
	 */
	private icons: Dictionary<Array<string>> = {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash
	}
	
	/**
	 * Computes page title depending on the user role
	 * @returns {string} Page title string
	 */
	get pageTitle(): string {
		return this.$store.getters['user/getRole'] === 'power' ? 
			this.$t('config.daemon.components.title').toString() : this.$t('config.selectedComponents.title').toString();
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
			.then((response) => {
				this.$store.commit('spinner/HIDE');
				if (this.$store.getters['user/getRole'] === 'power') {
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
			.catch((error) => FormErrorHandler.configError(error));
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
			component.enabled = enabled;
			DaemonConfigurationService.updateComponent(component.name, component)
				.then(() => {
					this.getConfig().then(() => {
						this.$toast.success(this.$t('config.daemon.components.messages.editSuccess', {component: component.name}).toString());
					});
				})
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
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
		if (enabled > 0) {
			return false;
		}
		return true;
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
					this.$toast.success(this.$t('config.components.form.messages.deleteSuccess', {component: component}).toString());
				});
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}

}
</script>
