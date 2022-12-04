<!--
Copyright 2017-2022 IQRF Tech s.r.o.
Copyright 2019-2022 MICRORISC s.r.o.

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
					<CIcon :content='icons.add' size='sm' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:fields='fields'
					:items='instances'
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
					:striped='true'
					:sorter='{ external: false, resetable: true }'
				>
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #acceptAsyncMsg='{item}'>
						<td>
							<CDropdown
								:color='item.acceptAsyncMsg ? "success": "danger"'
								:toggler-text='$t("states." + (item.acceptAsyncMsg ? "enabled": "disabled"))'
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
					<template #acceptOnlyLocalhost='{item}'>
						<td>
							<CDropdown
								:color='item.acceptOnlyLocalhost ? "success": "danger"'
								:toggler-text='$t("states." + (item.acceptOnlyLocalhost ? "enabled": "disabled"))'
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
								:toggler-text='$t("states." + (item.service.tlsEnabled !== undefined ?
									(item.service.tlsEnabled ? "enabled": "disabled") : "disabled"))'
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
								color='info'
								size='sm'
								:to='"/config/daemon/messagings/websocket/edit/" + item.instanceMessaging'
							>
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								color='danger'
								size='sm'
								@click='deleteInstance = {messaging: item.messaging.instance, service: item.service.instance}'
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
			:show='deleteInstance !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.daemon.messagings.websocket.interface.modal.title') }}
				</h5>
			</template>
			<div v-if='deleteInstance !== null'>
				{{ $t('config.daemon.messagings.websocket.interface.modal.prompt', {instance: deleteInstance.messaging}) }}
			</div>
			<template #footer>
				<CButton
					color='danger'
					@click='removeInterface()'
				>
					{{ $t('forms.delete') }}
				</CButton> <CButton
					color='secondary'
					@click='deleteInstance = null'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CDropdown, CDropdownItem, CIcon, CModal} from '@coreui/vue/src';

import {cilPlus, cilPencil, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IField} from '@/interfaces/coreui';
import {WsInterface, ModalInstance, IWsService, WsMessaging} from '@/interfaces/messagingInterfaces';

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
})

/**
 * Websocket interface list card for normal user
 */
export default class WebsocketInterfaceList extends Vue {
	/**
	 * @constant {ModalInstance} componentNames Websocket messaging and service component names
	 */
	private componentNames: ModalInstance = {
		messaging: 'iqrf::WebsocketMessaging',
		service: 'shape::WebsocketCppService',
	};

	/**
	 * @var {ModalInstance|null} deleteInstance Websocket interface instance used in remove modal
	 */
	private deleteInstance: ModalInstance|null = null;

	/**
	 * @constant {Array<IField>} fields CoreUI datatable columns
	 */
	private fields: Array<IField> = [
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
	 * @constant {Record<string, Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Record<string, Array<string>> = {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash
	};

	/**
	 * @var {Array<WsInterface>} instances Array of websocket interface instances
	 */
	private instances: Array<WsInterface> = [];

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.$store.commit('spinner/SHOW');
		this.getConfig();
	}

	/**
	 * Retrieves instances of Websocket daemon components
	 */
	private getConfig(): Promise<void> {
		return Promise.all([
			DaemonConfigurationService.getComponent(this.componentNames.messaging),
			DaemonConfigurationService.getComponent(this.componentNames.service),
		])
			.then((responses: Array<AxiosResponse>) => {
				const messagings = responses[0].data.instances;
				const services = responses[1].data.instances;
				let instances: Array<WsInterface> = [];
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
							instanceMessaging: messaging.instance,
							instanceService: service.instance,
							acceptAsyncMsg: messaging.acceptAsyncMsg,
							port: service.WebsocketPort,
							acceptOnlyLocalhost: service.acceptOnlyLocalhost,
						});
					}
				}
				this.instances = instances;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.websocket.interface.messages.listFailed'));
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
	 * @param {IWsInstance} service Websocket service instance
	 * @param {Record<string, boolean>} newSettings Settings to update instance with
	 */
	private editService(service: IWsService, newSettings: Record<string, boolean>): void {
		this.$store.commit('spinner/SHOW');
		let settings = {
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
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'config.daemon.messagings.websocket.interface.messages.updateFailed',
				{interface: settings.instance}
			));
	}

	/**
	 * Updates accepting asynchronous messages setting of Websocket messaging component instance
	 * @param {WsMessaging} instance Websocket messaging instance
	 * @param {boolean} setting new setting
	 */
	private changeAcceptAsyncMsg(instance: WsMessaging, setting: boolean): void {
		if (instance.acceptAsyncMsg === setting) {
			return;
		}
		this.$store.commit('spinner/SHOW');
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
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'config.daemon.messagings.websocket.interface.messages.updateFailed',
				{interface: instance.instance}
			));
	}

	/**
	 * Removes an existing instance of Websocket interface component
	 */
	private removeInterface(): void {
		if (this.deleteInstance === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		Promise.all([
			DaemonConfigurationService.deleteInstance(this.componentNames.messaging, this.deleteInstance.messaging),
			DaemonConfigurationService.deleteInstance(this.componentNames.service, this.deleteInstance.service),
		])
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.websocket.interface.messages.deleteSuccess', {instance: this.deleteInstance?.messaging})
							.toString()
					);
				});
				this.deleteInstance = null;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'config.daemon.messagings.websocket.interface.messages.deleteFailed',
					{interface: this.deleteInstance!.messaging}
				);
				this.deleteInstance = null;
			});
	}
}
</script>

<style scoped>
.card-header {
	padding-bottom: 0;
}
</style>

