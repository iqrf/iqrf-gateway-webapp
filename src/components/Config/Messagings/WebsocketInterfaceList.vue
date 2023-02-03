<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
			{{ $t('config.daemon.messagings.websocket.interface.title') }}
		</h1>
		<CCard>
			<CCardHeader class='border-0'>
				<CButton
					color='success'
					size='sm'
					class='float-right'
					to='/config/daemon/messagings/websocket/add'
				>
					<CIcon :content='cilPlus' size='sm' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:loading='loading'
					:fields='fields'
					:items='instances'
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
					:striped='true'
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #acceptAsyncMsg='{item}'>
						<td>
							<CDropdown
								:color='item.messaging.acceptAsyncMsg ? "success": "danger"'
								:toggler-text='$t(`states.${item.messaging.acceptAsyncMsg ? "enabled": "disabled"}`)'
								size='sm'
							>
								<CDropdownItem @click='changeAcceptAsyncMsg(item.messaging, true)'>
									{{ $t('states.enabled') }}
								</CDropdownItem>
								<CDropdownItem @click='changeAcceptAsyncMsg(item.messaging, false)'>
									{{ $t('states.disabled') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #instanceMessaging='{item}'>
						<td>
							{{ item.messaging.instance }}
						</td>
					</template>
					<template #port='{item}'>
						<td>
							{{ item.service.WebsocketPort }}
						</td>
					</template>
					<template #acceptOnlyLocalhost='{item}'>
						<td>
							<CDropdown
								:color='item.service.acceptOnlyLocalhost ? "success": "danger"'
								:toggler-text='$t(`states.${item.service.acceptOnlyLocalhost ? "enabled": "disabled"}`)'
								size='sm'
							>
								<CDropdownItem @click='changeAcceptOnlyLocalhost(item.service, true)'>
									{{ $t('states.enabled') }}
								</CDropdownItem>
								<CDropdownItem @click='changeAcceptOnlyLocalhost(item.service, false)'>
									{{ $t('states.disabled') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #tlsEnabled='{item}'>
						<td>
							<CDropdown
								:color='item.service.tlsEnabled ? "success": "danger"'
								:toggler-text='$t(`states.${(item.service.tlsEnabled ?? false) ? "enabled": "disabled"}`)'
								size='sm'
							>
								<CDropdownItem @click='changeTls(item.service, true)'>
									{{ $t('states.enabled') }}
								</CDropdownItem>
								<CDropdownItem @click='changeTls(item.service, false)'>
									{{ $t('states.disabled') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								class='mr-1'
								color='info'
								size='sm'
								:to='"/config/daemon/messagings/websocket/edit/" + item.messaging.instance'
							>
								<CIcon :content='cilPencil' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								color='danger'
								size='sm'
								@click='removeInstance(item.messaging.instance, item.service.instance)'
							>
								<CIcon :content='cilTrash' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<WebsocketInterfaceDeleteModal ref='deleteModal' @deleted='getConfig' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CDropdown, CDropdownItem, CIcon} from '@coreui/vue/src';
import WebsocketInterfaceDeleteModal from '@/components/Config/Messagings/WebsocketInterfaceDeleteModal.vue';

import {cilPlus, cilPencil, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IWsInterface, IWsMessaging, IWsService, ModalInstance} from '@/interfaces/Config/Messaging';
import {IField} from '@/interfaces/Coreui';

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
		WebsocketInterfaceDeleteModal,
	},
	data: () => ({
		cilPencil,
		cilPlus,
		cilTrash,
	}),
})

/**
 * Websocket interface list card for normal user
 */
export default class WebsocketInterfaceList extends Vue {
	/**
	 * @constant {ModalInstance} componentNames Websocket messaging and service component names
	 */
	private readonly componentNames: ModalInstance = {
		messaging: 'iqrf::WebsocketMessaging',
		service: 'shape::WebsocketCppService',
	};

	/**
	 * @constant {Array<IField>} fields CoreUI datatable columns
	 */
	private readonly fields: Array<IField> = [
		{
			key: 'instanceMessaging',
			label: this.$t('forms.fields.instanceName'),
		},
		{
			key: 'port',
			label: this.$t('config.daemon.messagings.websocket.form.WebsocketPort'),
		},
		{
			key: 'acceptAsyncMsg',
			label: this.$t('config.daemon.messagings.acceptAsyncMsg'),
			filter: false,
		},
		{
			key: 'acceptOnlyLocalhost',
			label: this.$t('config.daemon.messagings.websocket.form.acceptOnlyLocalhost'),
			filter: false,
		},
		{
			key: 'tlsEnabled',
			label: this.$t('config.daemon.messagings.websocket.form.tlsEnabled'),
			filter: false
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			filter: false,
			sorter: false,
		},
	];

	/**
	 * @var {boolean} loading Indicates that request is in progress
	 */
	private loading = false;

	/**
	 * @var {Array<IWsInterface>} instances Array of websocket interface instances
	 */
	private instances: Array<IWsInterface> = [];

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves instances of Websocket daemon components
	 */
	private getConfig(): Promise<void> {
		if (!this.loading) {
			this.loading = true;
		}
		return Promise.all([
			DaemonConfigurationService.getComponent(this.componentNames.messaging),
			DaemonConfigurationService.getComponent(this.componentNames.service),
		])
			.then((responses: Array<AxiosResponse>) => {
				const messagings = responses[0].data.instances;
				const services = responses[1].data.instances;
				const instances: Array<IWsInterface> = [];
				for (const messaging of messagings) {
					if (messaging.RequiredInterfaces === undefined ||
							messaging.RequiredInterfaces.length === 0 ||
							messaging.RequiredInterfaces[0].name !== 'shape::IWebsocketService' ||
							messaging.RequiredInterfaces[0].target.instance === undefined) {
						continue;
					}
					const serviceInstance = messaging.RequiredInterfaces[0].target.instance;
					for (const service of services) {
						if (service.instance !== serviceInstance) {
							continue;
						}
						instances.push({
							messaging: messaging,
							service: service,
						});
					}
				}
				this.instances = instances;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'config.daemon.messagings.websocket.interface.messages.listFailed');
			});
	}

	/**
	 * Updates accepted message source of Websocket service component instance
	 * @param {IWsService} service Websocket service instance
	 * @param {boolean} acceptOnlyLocalhost New setting
	 */
	private changeAcceptOnlyLocalhost(service: IWsService, acceptOnlyLocalhost: boolean): void {
		if (service.acceptOnlyLocalhost === acceptOnlyLocalhost) {
			return;
		}
		this.editService(service, {acceptOnlyLocalhost: acceptOnlyLocalhost});
	}

	/**
	 * Updates TLS enabled setting of Websocket service component instance
	 * @param {IWsService} service Websocket service instance
	 * @param {boolean} tlsEnabled New setting
	 */
	private changeTls(service: IWsService, tlsEnabled: boolean): void {
		if (service.tlsEnabled === tlsEnabled) {
			return;
		}
		this.editService(service, {tlsEnabled: tlsEnabled});
	}

	/**
	 * Saves changes in Websocket service instance configuration
	 * @param {IWsService} service Websocket service instance
	 * @param {Record<string, boolean>} newSettings Settings to update instance with
	 */
	private editService(service: IWsService, newSettings: Record<string, boolean>): void {
		this.loading = true;
		const settings = {
			...service,
			...newSettings,
		};
		DaemonConfigurationService.updateInstance(this.componentNames.service, settings.instance, settings)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.websocket.service.messages.updateSuccess', {service: settings.instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(
					error,
					'config.daemon.messagings.websocket.interface.messages.updateFailed',
					{interface: settings.instance}
				);
			});
	}

	/**
	 * Updates accepting asynchronous messages setting of Websocket messaging component instance
	 * @param {IWsMessaging} instance Websocket messaging instance
	 * @param {boolean} setting new setting
	 */
	private changeAcceptAsyncMsg(instance: IWsMessaging, setting: boolean): void {
		if (instance.acceptAsyncMsg === setting) {
			return;
		}
		this.loading = true;
		instance.acceptAsyncMsg = setting;
		DaemonConfigurationService.updateInstance(this.componentNames.messaging, instance.instance, instance)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.websocket.interface.messages.updateSuccess', {interface: instance.instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(
					error,
					'config.daemon.messagings.websocket.interface.messages.updateFailed',
					{interface: instance.instance}
				);
			});
	}

	/**
	 * Removes websocket interface instance
	 */
	private removeInstance(messaging: string, service: string): void {
		(this.$refs.deleteModal as WebsocketInterfaceDeleteModal).showModal(messaging, service);
	}
}
</script>
