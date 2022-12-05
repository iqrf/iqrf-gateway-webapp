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
			{{ $t('config.daemon.messagings.websocket.messaging.title') }}
		</h1>
		<CCard>
			<CCardHeader class='border-0'>
				<CButton
					color='success'
					size='sm'
					class='float-right'
					to='/config/daemon/messagings/websocket/add-messaging'
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
								<CDropdownItem @click='changeAccept(item, true)'>
									{{ $t('states.enabled') }}
								</CDropdownItem>
								<CDropdownItem @click='changeAccept(item, false)'>
									{{ $t('states.disabled') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #RequiredInterfaces='{item}'>
						<td>
							{{ item.RequiredInterfaces[0].target.instance }}
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='info'
								size='sm'
								:to='"/config/daemon/messagings/websocket/edit-messaging/" + item.instance'
							>
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								color='danger'
								size='sm'
								@click='deleteInstance = item.instance'
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
					{{ $t('config.daemon.messagings.websocket.messaging.modal.title') }}
				</h5>
			</template>
			{{ $t('config.daemon.messagings.websocket.messaging.modal.prompt', {messaging: deleteInstance}) }}
			<template #footer>
				<CButton
					color='danger'
					@click='removeInstance'
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
import {WsMessaging} from '@/interfaces/messagingInterfaces';

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
 * Websocket messaging list card for normal user
 */
export default class WebsocketMessagingList extends Vue {
	/**
	 * @constant {string} componentName Websocket messaging component name
	 */
	private componentName = 'iqrf::WebsocketMessaging';

	/**
	 * @var {string|null} deleteInstance Websocket messaging instance used in remove modal
	 */
	private deleteInstance: string|null = null;

	/**
	 * @constant {Array<IField>} fields CoreUI datatable columns
	 */
	private fields: Array<IField> = [
		{
			key: 'instance',
			label: this.$t('forms.fields.instanceName'),
		},
		{
			key: 'acceptAsyncMsg',
			label: this.$t('config.daemon.messagings.acceptAsyncMsg'),
		},
		{
			key: 'RequiredInterfaces',
			label: this.$t('config.daemon.messagings.websocket.form.requiredInterface.instance'),
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			filter: false,
			sorter: false,
		}
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
	 * @var {Array<WsMessaging>} instances Array of Websocket messaging instances
	 */
	private instances: Array<WsMessaging> = [];

	/**
	 * Vue lifecycle hook created
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves instances of Websocket messaging component
	 */
	private getConfig(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.instances = response.data.instances;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.messagings.websocket.messaging.messages.listFailed');
			});
	}

	/**
	 * Updates accepting asynchronous messages setting of Websocket messaging component instance
	 * @param {WsMessaging} instance Websocket messaging instance
	 * @param {boolean} setting new setting
	 */
	private changeAccept(instance: WsMessaging, setting: boolean): void {
		if (instance.acceptAsyncMsg !== setting) {
			const conf = {
				...instance,
			};
			conf.acceptAsyncMsg = setting;
			DaemonConfigurationService.updateInstance(this.componentName, instance.instance, conf)
				.then(() => {
					this.getConfig().then(() => {
						this.$toast.success(
							this.$t('config.daemon.messagings.websocket.messaging.messages.updateSuccess', {messaging: instance.instance})
								.toString()
						);
					});
				})
				.catch((error: AxiosError) => {
					extendedErrorToast(error, 'config.daemon.messagings.websocket.messaging.messages.updateFailed', {messaging: instance.instance});
				});
		}
	}

	/**
	 * Removes an existing instance of Websocket messaging component
	 */
	private removeInstance(): void {
		if (this.deleteInstance === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		const instance = this.deleteInstance;
		this.deleteInstance = null;
		DaemonConfigurationService.deleteInstance(this.componentName, instance)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.websocket.messaging.messages.deleteSuccess', {messaging: instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.messagings.websocket.messaging.messages.deleteFailed', {messaging: instance});
			});
	}
}
</script>

<style scoped>
.card-header {
	padding-bottom: 0;
}
</style>
