<template>
	<div>
		<CCard class='border-0'>
			<CCardHeader class='border-0'>
				<CButton
					color='success'
					size='sm'
					class='float-right'
					to='/config/daemon/monitor/add'
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
					<template #acceptOnlyLocalhost='{item}'>
						<td>
							<CDropdown
								:color='item.acceptOnlyLocalhost ? "success": "danger"'
								:toggler-text='$t("table.enabled." + item.acceptOnlyLocalhost)'
								size='sm'
							>
								<CDropdownItem @click='changeAcceptOnlyLocalhost(item.webSocket, true)'>
									{{ $t('table.enabled.true') }}
								</CDropdownItem>
								<CDropdownItem @click='changeAcceptOnlyLocalhost(item.webSocket, false)'>
									{{ $t('table.enabled.false') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #tlsEnabled='{item}'>
						<td>
							<CDropdown
								:color='item.tlsEnabled ? "success": "danger"'
								:toggler-text='$t("table.enabled." + (item.tlsEnabled !== undefined ? item.tlsEnabled : false))'
								size='sm'
							>
								<CDropdownItem @click='changeTls(item.webSocket, true)'>
									{{ $t('table.enabled.true') }}
								</CDropdownItem>
								<CDropdownItem @click='changeTls(item.webSocket, false)'>
									{{ $t('table.enabled.false') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='info'
								size='sm'
								:to='"/config/daemon/monitor/edit/" + item.monitor.instance'
							>
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								color='danger'
								size='sm'
								@click='deleteInstance = {monitor: item.monitor.instance, webSocket: item.webSocket.instance}'
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
					{{ $t('config.monitor.messages.delete.confirmTitle') }}
				</h5>
			</template>
			<div v-if='deleteInstance !== null'>
				{{ $t('config.monitor.messages.delete.confirm', {instance: deleteInstance.monitor}) }}
			</div>
			<template #footer>
				<CButton 
					color='danger'
					@click='deleteInstance = null'
				>
					{{ $t('forms.no') }}
				</CButton> <CButton
					color='success'
					@click='removeInterface()'
				>
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
import {Dictionary} from 'vue-router/types/router';
import {IField} from '../../interfaces/coreui';
import {AxiosError, AxiosResponse} from 'axios';
import {versionHigherThan} from '../../helpers/versionChecker';

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
	}
})

/**
 * List of monitoring component instances
 */
export default class MonitorList extends Vue {
	/**
	 * @constant {Dictionary<string>} componentNames Names of monitor and websocket components
	 */
	private componentNames: Dictionary<string> = {
		monitor: 'iqrf::MonitorService',
		webSocket: 'shape::WebsocketCppService'
	}

	/**
	 * @var {Dictionary<string>|null} deleteInstance Monitor component instance object used in remove modal
	 */
	private deleteInstance: Dictionary<string>|null = null

	/**
	 * @constant {Array<IField>} fields Array of CoreUI data table columns
	 */
	private fields: Array<IField> =  [
		{
			key: 'instance',
			label: this.$t('config.monitor.form.instance'),
		},
		{
			key: 'reportPeriod',
			label: this.$t('config.monitor.form.reportPeriod'),
		},
		{
			key: 'port',
			label: this.$t('config.monitor.form.WebsocketPort'),
		},
		{
			key: 'acceptOnlyLocalhost',
			label: this.$t('config.monitor.form.acceptOnlyLocalhost'),
			filter: false,
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			filter: false,
			sorter: false,
		},
	]

	/**
	 * @constant {Dictionary<Array<string>>} icons Array of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
	}

	/**
	 * @var {Array<unknown>} instances Array of monitoring component instances
	 */
	private instances: Array<unknown> = []

	/**
	 * Vue lifecycle hook created
	 */
	mounted(): void {
		if (versionHigherThan('2.3.0')) {
			this.fields.splice(4, 0, {
				key: 'tlsEnabled',
				label: this.$t('config.websocket.form.tlsEnabled'),
				filter: false,
			});
		}
		this.$store.commit('spinner/SHOW');
		this.getConfig();
	}

	/**
	 * Retrieves configuration of the monitoring component and websocket instance
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private getConfig(): Promise<void> {
		this.instances = [];
		return Promise.all([
			DaemonConfigurationService.getComponent(this.componentNames.monitor),
			DaemonConfigurationService.getComponent(this.componentNames.webSocket),
		])
			.then((responses: Array<AxiosResponse>) => {
				const monitors = responses[0].data.instances;
				const webSockets = responses[1].data.instances;
				let instances: Array<unknown> = [];
				for (const monitor of monitors) {
					if (monitor.RequiredInterfaces === undefined ||
							monitor.RequiredInterfaces === [] ||
							monitor.RequiredInterfaces[0].name !== 'shape::IWebsocketService' ||
							monitor.RequiredInterfaces[0].target.instance === undefined) {
						continue;
					}
					const webSocketInstance = monitor.RequiredInterfaces[0].target.instance;
					for (const webSocket of webSockets) {
						if (webSocket.instance !== webSocketInstance) {
							continue;
						}
						instances.push({
							monitor: monitor,
							webSocket: webSocket,
							instance: monitor.instance,
							reportPeriod: monitor.reportPeriod,
							acceptAsyncMsg: monitor.acceptAsyncMsg,
							port: webSocket.WebsocketPort,
							acceptOnlyLocalhost: webSocket.acceptOnlyLocalhost,
							tlsEnabled: webSocket.tlsEnabled
						});
					}
				}
				this.instances = instances;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}

	/**
	 * Updates configuration of the websocket service
	 * @param service WebSocket service instance
	 * @param {boolean} setting Message accepting setting
	 */
	private changeAcceptOnlyLocalhost(service, setting: boolean): void {
		if (service.acceptOnlyLocalhost === setting) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		service.acceptOnlyLocalhost = setting;
		DaemonConfigurationService.updateInstance(this.componentNames.webSocket, service.instance, service)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.monitor.messages.edit.success', {instance: service.instance})
							.toString()
					);
				});
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}

	/**
	 * Removes instance of the monitoring component
	 */
	private removeInterface(): void {
		if (this.deleteInstance === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		Promise.all([
			DaemonConfigurationService.deleteInstance(this.componentNames.monitor, this.deleteInstance.monitor),
			DaemonConfigurationService.deleteInstance(this.componentNames.webSocket, this.deleteInstance.webSocket),
		])
			.then(() => {
				if (this.deleteInstance === null) {
					return;
				}
				const deleteInstance = this.deleteInstance;
				this.deleteInstance = null;
				this.getConfig()
					.then(() => this.$toast.success(
						this.$t('config.monitor.messages.delete.success', {instance: deleteInstance.monitor})
							.toString())
					);
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}
}
</script>

<style scoped>

.card-header {
	padding-bottom: 0;
}

</style>
