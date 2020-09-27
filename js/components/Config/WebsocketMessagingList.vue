<template>
	<div>
		<CCard>
			<CCardHeader>
				{{ $t('config.websocket.messaging.title') }}
				<CButton
					color='success'
					size='sm'
					class='float-right'
					to='/config/websocket/add-messaging'
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
					<template #acceptAsyncMsg='{item}'>
						<td>
							<CDropdown
								:color='item.acceptAsyncMsg ? "success": "danger"'
								:toggler-text='item.acceptAsyncMsg ? $t("config.websocket.enabled") : $t("config.websocket.disabled")'
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
								:to='"/config/websocket/edit-messaging/" + item.instance'
							>
								<CIcon :content='$options.icons.edit' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								color='danger'
								size='sm'
								@click='modals.instance = item.instance'
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
			:show.sync='modals.instance !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.websocket.messaging.messages.deleteTitle') }}
				</h5>
			</template>
			{{ $t('config.websocket.messaging.messages.deletePrompt', {messaging: modals.instance}) }}
			<template #footer>
				<CButton color='danger' @click='modals.instance = null'>
					{{ $t('forms.no') }}
				</CButton>
				<CButton color='success' @click='removeInstance'>
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
	name: 'WebsocketMessagingList',
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
			componentName: 'iqrf::WebsocketMessaging',
			fields: [
				{
					key: 'instance',
					label: this.$t('config.websocket.form.instance'),
				},
				{
					key: 'acceptAsyncMsg',
					label: this.$t('config.websocket.form.acceptAsyncMsg'),
				},
				{
					key: 'RequiredInterfaces',
					label: this.$t('config.websocket.form.requiredInterface.instance'),
				},
				{
					key: 'actions',
					label: this.$t('table.actions.title'),
					filter: false,
					sorter: false,
				}
			],
			instances: [],
			modals: {
				instance: null,
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
		changeAccept(instance, setting) {
			if (instance.acceptAsyncMsg !== setting) {
				instance.acceptAsyncMsg = setting;
				DaemonConfigurationService.updateInstance(this.componentName, instance.instance, instance)
					.then(() => {
						this.getConfig().then(() => {
							this.$toast.success(this.$t('config.websocket.messaging.messages.editSuccess', {messaging: instance.instance}).toString());
						});
					})
					.catch((error) => FormErrorHandler.getConfig(error));
			}
		},
		removeInstance() {
			this.$store.commit('spinner/SHOW');
			const instance = this.modals.instance;
			this.modals.instance = null;
			DaemonConfigurationService.deleteInstance(this.componentName, instance)
				.then(() => {
					this.getConfig().then(() => {
						this.$toast.success(this.$t('config.websocket.messaging.messages.deleteSuccess', {messaging: instance}).toString());
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
};
</script>
