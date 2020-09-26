<template>
	<div>
		<CCard>
			<CCardHeader>
				{{ $t('config.websocket.service.title') }}
				<CButton
					color='success'
					size='sm'
					class='float-right'
					to='/config/websocket/add-service'
				>
					<CIcon :content='$options.icons.add' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:fields='fields'
					:items='instances'
				>
					<template #acceptOnlyLocalhost='{item}'>
						<td>
							<CDropdown
								:color='item.acceptOnlyLocalhost ? "success": "danger"'
								:toggler-text='item.acceptOnlyLocalhost ? $t("config.websocket.enabled") : $t("config.websocket.disabled")'
								size='sm'
							>
								<CDropdownItem @click='changeAccept(item, true)'>
									{{ $t('config.websocket.enabled') }}
								</CDropdownItem>
								<CDropdownItem @click='changeAccept(item, false)'>
									{{ $t('config.websocket.disabled') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='info'
								size='sm'
								:to='"/config/websocket/edit-service/" + item.instance'
							>
								<CIcon :content='$options.icons.edit' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								color='danger'
								size='sm'
								@click='modals.service = item.instance'
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
			:show.sync='modals.service !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.websocket.service.messages.deleteTitle') }}
				</h5>
			</template>
			{{ $t('config.websocket.service.messages.deletePrompt', {service: modals.service}) }}
			<template #footer>
				<CButton color='danger' @click='modals.service = null'>
					{{ $t('forms.no') }}
				</CButton>
				<CButton color='success' @click='removeService'>
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
	name: 'WebsocketServiceList',
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
			componentName: 'shape::WebsocketCppService',
			fields: [
				{key: 'instance', label: this.$t('config.websocket.form.instance')},
				{key: 'WebsocketPort', label: this.$t('config.websocket.form.WebsocketPort')},
				{key: 'acceptOnlyLocalhost', label: this.$t('config.websocket.form.acceptOnlyLocalhost')},
				{key: 'actions', label: this.$t('table.actions.title')},
			],
			instances: null,
			modals: {
				service: null,
			},
		};
	},
	created() {
		this.getConfig();
	},
	methods: {
		getConfig() {
			this.$store.commit('spinner/SHOW');
			return DaemonConfigurationService.getComponent(this.componentName)
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					this.instances = response.data.instances;
				})
				.catch((error) => FormErrorHandler.configError(error));
		},
		changeAccept(service, setting) {
			if (service.acceptOnlyLocalhost !== setting) {
				service.acceptOnlyLocalhost = setting;
				DaemonConfigurationService.updateInstance(this.componentName, service.instance, service)
					.then(() => {
						this.getConfig().then(() => {
							this.$toast.success(this.$t('config.websocket.service.messages.editSuccess', {service: service.instance}).toString());
						});
					})
					.catch((error) => FormErrorHandler.getConfig(error));
			}
		},
		removeService() {
			this.$store.commit('spinner/SHOW');
			const service = this.modals.service;
			this.modals.service = null;
			DaemonConfigurationService.deleteInstance(this.componentName, service)
				.then(() => {
					this.getConfig().then(() => {
						this.$toast.success(this.$t('config.websocket.service.messages.deleteSuccess', {service: service}).toString());
					});
				})
				.catch((error) => FormErrorHandler.configError(error));
		}
	},
	icons: {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
	},
	metaInfo: {
		title: 'config.websocket.title',
	},
};
</script>
