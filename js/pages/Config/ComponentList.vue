<template>
	<div>
		<CCard>
			<CCardHeader>
				<CButton
					color='success'
					size='sm'
					class='float-right'
					to='/config/component/add'
				>
					<CIcon :content='$options.icons.add' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:fields='fields'
					:items='components'
				>
					<template #enabled='{item}'>
						<td>
							<CDropdown
								:color='item.enabled ? "success" : "danger"'
								:toggler-text='item.enabled ? $t("config.components.form.enabled") : $t("config.components.form.disabled")'
								size='sm'
							>
								<CDropdownItem color='success' @click='changeEnabled(item, true)'>
									{{ $t('config.components.form.enabled') }}
								</CDropdownItem>
								<CDropdownItem color='danger' @click='changeEnabled(item, false)'>
									{{ $t('config.components.form.disabled') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='info'
								size='sm'
								:to='"/config/component/edit/" + item.name'
							>
								<CIcon :content='$options.icons.edit' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								color='danger'
								size='sm'
								@click='modals.component = item.name'
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
			:show.sync='modals.component !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.components.form.messages.deleteTitle') }}
				</h5>
			</template>
			{{ $t('config.components.form.messages.deletePrompt', {component: modals.component}) }}
			<template #footer>
				<CButton color='danger' @click='modals.component = null'>
					{{ $t('forms.no') }}
				</CButton>
				<CButton color='success' @click='removeComponent'>
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
	name: 'ComponentList',
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
			components: null,
			fields: [
				{key: 'name', label: this.$t('config.components.form.name')},
				{key: 'startlevel', label: this.$t('config.components.form.startLevel')},
				{key: 'libraryPath', label: this.$t('config.components.form.libraryPath')},
				{key: 'libraryName', label: this.$t('config.components.form.libraryName')},
				{key: 'enabled', label: this.$t('config.components.form.enabled')},
				{key: 'actions', label: this.$t('table.actions.title')},
			],
			modals: {
				component: null,
			},
		};
	},
	created() {
		this.getConfig();
	},
	methods: {
		getConfig() {
			this.$store.commit('spinner/SHOW');
			return DaemonConfigurationService.getComponent('')
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					this.components = response.data.components;
				})
				.catch((error) => FormErrorHandler.configError(error));
		},
		changeEnabled(component, enabled) {
			if (component.enabled !== enabled) {
				component.enabled = enabled;
				DaemonConfigurationService.updateComponent(component.name, component)
					.then(() => {
						this.getConfig().then(() => {
							this.$toast.success(this.$t('config.components.form.messages.editSuccess', {component: component.name}).toString());
						});
					})
					.catch((error) => FormErrorHandler.configError(error));
			}
		},
		removeComponent() {
			this.$store.commit('spinner/SHOW');
			const component = this.modals.component;
			this.modals.component = null;
			DaemonConfigurationService.deleteComponent(component)
				.then(() => {
					this.getConfig().then(() => {
						this.$toast.success(this.$t('config.components.form.messages.deleteSuccess', {component: component}).toString());
					});
				})
				.catch((error) => FormErrorHandler.configError(error));
		},
	},
	icons: {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash
	},
	metaInfo: {
		title: 'config.components.title',
	},
};
</script>
