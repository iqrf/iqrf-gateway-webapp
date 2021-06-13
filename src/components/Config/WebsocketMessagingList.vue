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
					:title='$t("table.actions.add")'
				>
					<CIcon :content='icons.add' size='sm' />
					<span class='d-none d-lg-inline'>
						{{ $t('table.actions.add') }}
					</span>
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
								:title='$t("table.actions.edit")'
							>
								<CIcon :content='icons.edit' size='sm' />
								<span class='d-none d-lg-inline'>
									{{ $t('table.actions.edit') }}
								</span>
							</CButton> <CButton
								color='danger'
								size='sm'
								:title='$t("table.actions.delete")'
								@click='deleteInstance = item.instance'
							>
								<CIcon :content='icons.remove' size='sm' />
								<span class='d-none d-lg-inline'>
									{{ $t('table.actions.delete') }}
								</span>
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
					{{ $t('config.daemon.messagings.websocket.messaging.messages.deleteTitle') }}
				</h5>
			</template>
			{{ $t('config.daemon.messagings.websocket.messaging.messages.deletePrompt', {messaging: deleteInstance}) }}
			<template #footer>
				<CButton
					color='danger'
					@click='deleteInstance = null'
				>
					{{ $t('forms.no') }}
				</CButton> <CButton
					color='success'
					@click='removeInstance'
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
import {cilPlus, cilPencil, cilTrash} from '@coreui/icons';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {IField} from '../../interfaces/coreui';
import {WsMessaging} from '../../interfaces/messagingInterfaces';
import { AxiosError, AxiosResponse } from 'axios';
import { Dictionary } from 'vue-router/types/router';

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
	private componentName = 'iqrf::WebsocketMessaging'

	/**
	 * @var {string|null} deleteInstance Websocket messaging instance used in remove modal
	 */
	private deleteInstance: string|null = null

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
	]

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash
	}

	/**
	 * @var {Array<WsMessaging>} instances Array of Websocket messaging instances
	 */
	private instances: Array<WsMessaging> = []

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
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}

	/**
	 * Updates accepting asynchronous messages setting of Websocket messaging component instance
	 * @param {WsMessaging} instance Websocket messaging instance
	 * @param {boolean} setting new setting
	 */
	private changeAccept(instance: WsMessaging, setting: boolean): void {
		if (instance.acceptAsyncMsg !== setting) {
			instance.acceptAsyncMsg = setting;
			DaemonConfigurationService.updateInstance(this.componentName, instance.instance, instance)
				.then(() => {
					this.getConfig().then(() => {
						this.$toast.success(
							this.$t('config.daemon.messagings.websocket.messaging.messages.editSuccess', {messaging: instance.instance})
								.toString()
						);
					});
				})
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
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
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}
}
</script>

<style scoped>

.card-header {
	padding-bottom: 0;
}

</style>
