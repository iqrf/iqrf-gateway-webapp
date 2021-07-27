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
		<h1>{{ $t('core.ssh.title') }}</h1>
		<CCard>
			<CCardBody>
				<CDataTable
					:items='keys'
					:fields='fields'
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
					:striped='true'
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #description='{item}'>
						<td>
							{{ item.description === null ? 'None' : item.description }}
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='info'
								size='sm'
								:to='"/ssh-key/edit/" + item.id'
							>
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								color='danger'
								size='sm'
							>
								<CIcon :content='icons.delete' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CModal} from '@coreui/vue/src';

import {cilPlus, cilTrash, cilPencil} from '@coreui/icons';
import {extendedErrorToast} from '../../helpers/errorToast';

import SshService from '../../services/SshService';

import {AxiosResponse, AxiosError} from 'axios';
import {Dictionary} from 'vue-router/types/router';
import {IField} from '../../interfaces/coreui';
import {ISshKeyList} from '../../interfaces/ssh';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CModal,
	},
	metaInfo: {
		title: 'core.ssh.title',
	},
})

/**
 * SSH key list component
 */
export default class SshKeyList extends Vue {

	/**
	 * @var {Array<ISshKeyList>} keys List of authorized SSH public keys
	 */
	private keys: Array<ISshKeyList> = []

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		add: cilPlus,
		delete: cilTrash,
		edit: cilPencil,
	}

	/**
	 * @constant {Array<IField>} fields Array of CoreUI data table columns
	 */
	private fields: Array<IField> = [
		{
			key: 'description',
			label: this.$t('core.ssh.table.description').toString(),
		},
		{
			key: 'type',
			label: this.$t('core.ssh.table.type').toString(),
		},
		{
			key: 'createdAt',
			label: this.$t('core.ssh.table.createdAt').toString(),
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title').toString(),
		},
	]

	/**
	 * Retrieves list of authorized SSH public keys
	 */
	mounted(): void {
		SshService.listKeys()
			.then((response: AxiosResponse) => {
				this.keys = response.data;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.ssh.messages.listFailed'));
	}

}
</script>
