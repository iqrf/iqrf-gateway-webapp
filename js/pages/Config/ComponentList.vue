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
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
					:striped='true'
					:sorter='{ external: false, resetable: true }'
				>
					<template #enabled='{item}'>
						<td>
							<CDropdown
								:color='item.enabled ? "success" : "danger"'
								:toggler-text='item.enabled ? $t("config.components.form.enabled") : $t("config.components.form.disabled")'
								size='sm'
							>
								<CDropdownItem @click='changeEnabled(item, true)'>
									{{ $t('config.components.form.enabled') }}
								</CDropdownItem>
								<CDropdownItem @click='changeEnabled(item, false)'>
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
				{
					key: 'enabled',
					label: this.$t('config.components.form.enabled'),
					filter: false,
				},
				{
					key: 'actions',
					label: this.$t('table.actions.title'),
					filter: false,
					sorter: false,
				},
			],
			modals: {
				component: null,
			},
		};
	},
	created() {
		this.getConfig();
	},
	mounted() {
		const titleEl = document.getElementById('title');
		if (titleEl !== null) {
			titleEl.innerText = this.$t(this.$metaInfo.title).toString();
		}
	},
	methods: {
		getConfig() {
			this.$store.commit('spinner/SHOW');
			return DaemonConfigurationService.getComponent('')
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					if (this.$store.getters['user/getRole'] === 'power') {
						this.components = response.data.components;
					} else {
						const whitelistedComponents = ['iqrf::IqrfCdc', 'iqrf::IqrfSpi', 'iqrf::IqrfUart'];
						this.components = response.data.components.filter((component) => {
							if (whitelistedComponents.includes(component.name)) {
								return component;
							}
						});
					}
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
	metaInfo() {
		return {
			title: this.$store.getters['user/getRole'] === 'power' ?
				'config.components.title' :
				'config.selectedComponents.title',
		};
	},
};
</script>
