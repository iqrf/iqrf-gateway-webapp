<template>
	<div>
		<CCard>
			<CCardHeader>
				<CButton
					color='success'
					size='sm'
					class='float-right'
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
								<CDropdownItem color='success'>
									{{ $t('config.components.form.enabled') }}
								</CDropdownItem>
								<CDropdownItem color='danger'>
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
							>
								<CIcon :content='$options.icons.edit' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								color='danger'
								size='sm'
							>
								<CIcon :content='$options.icons.remove' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
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
			]
		};
	},
	created() {
		this.getConfig();
	},
	methods: {
		getConfig() {
			DaemonConfigurationService.getComponent('')
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					this.components = response.data.components;
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
		title: 'config.main.title',
	},
};
</script>
