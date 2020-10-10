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
					<CIcon :content='getIcon("add")' />
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
								:toggler-text='$t("table.enabled." + item.acceptAsyncMsg)'
								size='sm'
							>
								<CDropdownItem @click='changeAccept(item, true)'>
									{{ $t('table.enabled.true') }}
								</CDropdownItem>
								<CDropdownItem @click='changeAccept(item, false)'>
									{{ $t('table.enabled.false') }}
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
								<CIcon :content='getIcon("edit")' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								color='danger'
								size='sm'
								@click='modals.instance = item.instance'
							>
								<CIcon :content='getIcon("remove")' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			color='danger'
			:show='modals.instance !== null'
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

<script lang='ts'>
import Vue from 'vue';
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CDropdown, CDropdownItem, CIcon, CModal} from '@coreui/vue/src';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {IField} from '../../interfaces/IField';
import {getCoreIcon} from '../../helpers/icons';
import { AxiosError, AxiosResponse } from 'axios';

interface IRequiredInterface {
	name: string
	target: Record<string, string>
}

interface IWsMessagingInstance {
	RequiredInterfaces: Array<IRequiredInterface>
	acceptAsyncMsg: boolean
	component: string
	instance: string
}

interface IWsMessagingList {
	componentName: string
	fields: Array<IField>
	instances: Array<IWsMessagingInstance>
	modals: Record<string, string|null>
}

export default Vue.extend({
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
	data(): IWsMessagingList {
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
		getIcon(icon: string): void|string[] {
			return getCoreIcon(icon);
		},
		getConfig(): Promise<void> {
			this.$store.commit('spinner/SHOW');
			return DaemonConfigurationService.getComponent(this.componentName)
				.then((response: AxiosResponse) => {
					this.$store.commit('spinner/HIDE');
					this.instances = response.data.instances;
				})
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		},
		changeAccept(instance, setting): void {
			if (instance.acceptAsyncMsg !== setting) {
				instance.acceptAsyncMsg = setting;
				DaemonConfigurationService.updateInstance(this.componentName, instance.instance, instance)
					.then(() => {
						this.getConfig().then(() => {
							this.$toast.success(
								this.$t('config.websocket.messaging.messages.editSuccess', {messaging: instance.instance})
									.toString()
							);
						});
					})
					.catch((error: AxiosError) => FormErrorHandler.configError(error));
			}
		},
		removeInstance(): void {
			if (this.modals.instance === null) {
				return;
			}
			this.$store.commit('spinner/SHOW');
			const instance = this.modals.instance;
			this.modals.instance = null;
			DaemonConfigurationService.deleteInstance(this.componentName, instance)
				.then(() => {
					this.getConfig().then(() => {
						this.$toast.success(
							this.$t('config.websocket.messaging.messages.deleteSuccess', {messaging: instance})
								.toString()
						);
					});	
				})
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		}
	},
});
</script>

<style scoped>
.btn {
  margin: 0 3px 0 0;
}
</style>
