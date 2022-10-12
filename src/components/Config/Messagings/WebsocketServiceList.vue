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
			{{ $t('config.daemon.messagings.websocket.service.title') }}
		</h1>
		<CCard>
			<CCardHeader class='border-0'>
				<CButton
					color='success'
					size='sm'
					class='float-right'
					to='/config/daemon/messagings/websocket/add-service'
				>
					<CIcon :content='cilPlus' size='sm' />
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
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #acceptOnlyLocalhost='{item}'>
						<td>
							<CDropdown
								:color='item.acceptOnlyLocalhost ? "success": "danger"'
								:toggler-text='$t(`states.${item.acceptOnlyLocalhost ? "enabled": "disabled"}`)'
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
					<template #tlsEnabled='{item}'>
						<td>
							<CDropdown
								:color='item.tlsEnabled ? "success": "danger"'
								:toggler-text='$t(`states.${item.tlsEnabled ? "enabled": "disabled"}`)'
								size='sm'
							>
								<CDropdownItem @click='changeTLS(item, true)'>
									{{ $t('states.enabled') }}
								</CDropdownItem>
								<CDropdownItem @click='changeTLS(item, false)'>
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
								:to='"/config/daemon/messagings/websocket/edit-service/" + item.instance'
							>
								<CIcon :content='cilPencil' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<WebsocketDeleteModal
								:component-type='WebsocketTypes.SERVICE'
								:instance='item'
								@deleted='getConfig'
							/>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue, Watch} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CDropdown, CDropdownItem, CIcon, CModal} from '@coreui/vue/src';
import WebsocketDeleteModal from '@/components/Config/Messagings/WebsocketDeleteModal.vue';

import {cilPencil, cilPlus} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';
import {mapGetters} from 'vuex';
import {versionHigherEqual} from '@/helpers/versionChecker';
import {WebsocketTypes} from '@/enums/Config/Messagings';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IField} from '@/interfaces/coreui';
import {IWsService} from '@/interfaces/Config/Messaging';

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
		WebsocketDeleteModal
	},
	computed: {
		...mapGetters({
			daemonVersion: 'daemonClient/getVersion',
		}),
	},
	data: () => ({
		cilPencil,
		cilPlus,
		WebsocketTypes,
	}),
})

/**
 * Websocket service list card for normal user
 */
export default class WebsocketServiceList extends Vue {
	/**
	 * @constant {string} componentName Websocket service component name
	 */
	private componentName = 'shape::WebsocketCppService';

	/**
	 * @constant {Array<IField>} fields CoreUI datatable columns
	 */
	private fields: Array<IField> = [
		{
			key: 'instance',
			label: this.$t('forms.fields.instanceName'),
		},
		{
			key: 'WebsocketPort',
			label: this.$t('config.daemon.messagings.websocket.form.WebsocketPort'),
		},
		{
			key: 'acceptOnlyLocalhost',
			label: this.$t('config.daemon.messagings.websocket.form.acceptOnlyLocalhost'),
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			filter: false,
			sorter: false,
		},
	];

	/**
	 * @var {Array<WsService>} instances Array of Websocket service instances
	 */
	private instances: Array<IWsService> = [];

	/**
	 * Daemon version computed property watcher to re-render elements dependent on version
	 */
	@Watch('daemonVersion')
	private updateTable(): void {
		if (versionHigherEqual('2.3.0')) {
			this.fields.splice(3, 0, {
				key: 'tlsEnabled',
				label: this.$t('config.daemon.messagings.websocket.form.tlsEnabled')
			});
		}
	}

	/**
	 * Vue lifecycle hook created
	 */
	mounted(): void {
		this.updateTable();
		this.getConfig();
	}

	/**
	 * Retrieves instances of Websocket service component
	 */
	private getConfig(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.instances = response.data.instances;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.messagings.websocket.service.messages.getFailed');
			});
	}

	/**
	 * Updates accepted message source of Websocket service component instance
	 * @param {IWsService} service Websocket service instance
	 * @param {boolean} acceptOnlyLocalhost New setting
	 */
	private changeAccept(service: IWsService, acceptOnlyLocalhost: boolean): void {
		if (service.acceptOnlyLocalhost === acceptOnlyLocalhost) {
			return;
		}
		this.edit(service, {acceptOnlyLocalhost: acceptOnlyLocalhost});
	}

	/**
	 * Updates TLS enabled setting of Websocket service component instance
	 * @param {IWsService} service Websocket service instance
	 * @param {boolean} tlsEnabled New setting
	 */
	private changeTLS(service: IWsService, tlsEnabled: boolean): void {
		if (service.tlsEnabled === tlsEnabled) {
			return;
		}
		this.edit(service, {tlsEnabled: tlsEnabled});
	}

	/**
	 * Saves changes in Websocket service instance configuration
	 * @param {IWsService} service Websocket service instance
	 * @param {Record<string, boolean>} newSettings Settings to update instance with
	 */
	private edit(service: IWsService, newSettings: Record<string, boolean>): void {
		this.$store.commit('spinner/SHOW');
		const settings = {
			...service,
			...newSettings,
		};
		DaemonConfigurationService.updateInstance(this.componentName, settings.instance, settings)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.messagings.websocket.service.messages.updateSuccess', {service: settings.instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.messagings.websocket.service.messages.updateFailed', {service: settings.instance});
			});
	}
}
</script>
