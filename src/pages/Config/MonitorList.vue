<template>
	<div>
		<h1>{{ $t('config.monitor.title') }}</h1>
		<CCard>
			<CCardHeader>
				<CButton
					color='success'
					size='sm'
					class='float-right'
					to='/config/monitor/add'
				>
					<CIcon :content='$options.icons.add' />
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
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='info'
								size='sm'
								:to='"/config/monitor/edit/" + item.monitor.instance'
							>
								<CIcon :content='$options.icons.edit' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								color='danger'
								size='sm'
								@click='modals.instances = {monitor: item.monitor.instance, webSocket: item.webSocket.instance}'
							>
								<CIcon :content='$options.icons.remove' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			color='danger'
			:show='modals.instances !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.monitor.messages.delete.confirmTitle') }}
				</h5>
			</template>
			<div v-if='modals.instances !== null'>
				{{ $t('config.monitor.messages.delete.confirm', {instance: modals.instances.monitor}) }}
			</div>
			<template #footer>
				<CButton color='danger' @click='modals.instances = null'>
					{{ $t('forms.no') }}
				</CButton>
				<CButton color='success' @click='removeInterface()'>
					{{ $t('forms.yes') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script>
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CDropdown, CDropdownItem, CIcon, CModal} from '@coreui/vue/src';
import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';

export default {
	name: 'MonitorList',
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
	data() {
		return {
			componentNames: {
				monitor: 'iqrf::MonitorService',
				webSocket: 'shape::WebsocketCppService',
			},
			fields: [
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
			],
			instances: [],
			modals: {
				instances: null,
			},
		};
	},
	created() {
		this.$store.commit('spinner/SHOW');
		this.getConfig();
	},
	methods: {
		getConfig() {
			this.instances = [];
			return Promise.all([
				DaemonConfigurationService.getComponent(this.componentNames.monitor),
				DaemonConfigurationService.getComponent(this.componentNames.webSocket),
			])
				.then((responses) => {
					const monitors = responses[0].data.instances;
					const webSockets = responses[1].data.instances;
					console.error(monitors);
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
							this.instances.push({
								monitor: monitor,
								webSocket: webSocket,
								instance: monitor.instance,
								reportPeriod: monitor.reportPeriod,
								acceptAsyncMsg: monitor.acceptAsyncMsg,
								port: webSocket.WebsocketPort,
								acceptOnlyLocalhost: webSocket.acceptOnlyLocalhost,
							});
						}
					}
					this.$store.commit('spinner/HIDE');
				});
			// TODO: add error message
		},
		changeAcceptOnlyLocalhost(service, setting) {
			this.$store.commit('spinner/SHOW');
			service.acceptOnlyLocalhost = setting;
			DaemonConfigurationService.updateInstance(this.componentNames.webSocket, service.instance, service)
				.then(() => {
					this.getConfig().then(() => {
						this.$toast.success(
							this.$t('config.monitor.service.messages.editSuccess', {service: service.instance})
								.toString()
						);
					});
				})
				.catch((error) => FormErrorHandler.configError(error));
		},
		removeInterface() {
			this.$store.commit('spinner/SHOW');
			Promise.all([
				DaemonConfigurationService.deleteInstance(this.componentNames.monitor, this.modals.instances.monitor),
				DaemonConfigurationService.deleteInstance(this.componentNames.webSocket, this.modals.instances.webSocket),
			])
				.then(() => {
					this.modals.instances = null;
					this.getConfig()
						.then(() => this.$toast.success(
							this.$t('config.monitor.messages.delete.success', {instance: this.modals.instances.monitor})
								.toString())
						);
				});
			// TODO: add error message
		},
	},
	icons: {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
	},
	metaInfo: {
		title: 'config.monitor.title'
	}
};
</script>
