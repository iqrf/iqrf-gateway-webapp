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
			<CCardHeader class='datatable-header'>
				<div>
					{{ $t('core.ssh.table.title') }}
				</div>
				<div>
					<CButton
						color='success'
						to='/ssh-key/add/'
						size='sm'
					>
						<CIcon :content='icons.add' size='sm' />
						<span class='d-none dg-lg-inline'>
							{{ $t('core.ssh.table.add') }}
						</span>
					</CButton>
				</div>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:items='keys'
					:fields='fields'
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
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
								@click='item.showDetails = !item.showDetails'
							>
								<CIcon :content='icons.info' size='sm' />
								<span class='d-none dg-lg-inline'>
									{{ $t('table.actions.details') }}
								</span>
							</CButton> <CButton
								color='danger'
								size='sm'
								@click='keyToDelete = item'
							>
								<CIcon :content='icons.delete' size='sm' />
								<span class='d-none dg-lg-inline'>
									{{ $t('table.actions.delete') }}
								</span>
							</CButton>
						</td>
					</template>
					<template #details='{item}'>
						<CCollapse :show='item.showDetails'>
							<CCardBody class='datatable-collapse'>
								<table>
									<tbody>
										<tr>
											<th>{{ $t('core.ssh.table.type') }}</th>
											<td>{{ item.type }}</td>
										</tr>
										<tr>
											<th>{{ $t('core.ssh.table.hash') }}</th>
											<td>{{ item.hash }}</td>
										</tr>
										<tr>
											<th>
												{{ $t('core.ssh.table.key') }}
												<CButton
													color='primary'
													size='sm'
												>
													<CIcon :content='icons.copy' size='sm' />
												</CButton>
											</th>
											<td style='max-width: 60vw;'>
												{{ item.key }}
											</td>
										</tr>
									</tbody>
								</table>
							</CCardBody>
						</CCollapse>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			color='danger'
			:show='keyToDelete !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('core.ssh.modal.title') }}
				</h5>
			</template>
			<span v-if='keyToDelete !== null'>
				{{ $t('core.ssh.modal.prompt', {id: keyToDelete.id}) }}
			</span>
			<template #footer>
				<CButton
					color='danger'
					@click='deleteKey(keyToDelete.id)'
				>
					{{ $t('forms.delete') }}
				</CButton> <CButton
					color='secondary'
					@click='keyToDelete = null'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CCollapse, CDataTable, CIcon, CModal} from '@coreui/vue/src';

import {cilPlus, cilTrash, cilInfo, cilClipboard} from '@coreui/icons';
import {extendedErrorToast} from '../../helpers/errorToast';

import SshService from '../../services/SshService';

import {AxiosResponse, AxiosError} from 'axios';
import {Dictionary} from 'vue-router/types/router';
import {IField} from '../../interfaces/coreui';
import {ISshKey} from '../../interfaces/ssh';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CCollapse,
		CDataTable,
		CIcon,
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
	 * @var {Array<ISshKey>} keys List of authorized SSH public keys
	 */
	private keys: Array<ISshKey> = []

	/**
	 * @var {ISshKey|null} keyToDelete 
	 */
	private keyToDelete: ISshKey|null = null

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		add: cilPlus,
		copy: cilClipboard,
		delete: cilTrash,
		info: cilInfo,
	}

	/**
	 * @constant {Array<IField>} fields Array of CoreUI data table columns
	 */
	private fields: Array<IField> = [
		{
			key: 'id',
			label: this.$t('core.ssh.table.id').toString(),
		},
		{
			key: 'description',
			label: this.$t('core.ssh.table.description').toString(),
		},
		{
			key: 'createdAt',
			label: this.$t('core.ssh.table.createdAt').toString(),
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title').toString(),
			sorter: false,
			filter: false,
		},
	]

	/**
	 * Retrieves list of authorized SSH public keys
	 */
	mounted(): void {
		this.listKeys();
	}

	/**
	 * Retrieves list of authorized SSH public keys
	 */
	private listKeys(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return SshService.listKeys()
			.then((response: AxiosResponse) => {
				let keys: Array<ISshKey> = response.data;
				for (let i in keys) {
					keys[i].showDetails = false;
				}
				this.keys = response.data;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.ssh.messages.listFailed'));
	}

	/**
	 * Removes authorized SSH public key
	 * @param {number} id SSH public key ID
	 */
	private deleteKey(id: number): void {
		this.keyToDelete = null;
		this.$store.commit('spinner/SHOW');
		SshService.deleteKey(id)
			.then(() => {
				this.listKeys().then(() => this.$toast.success(
					this.$t('core.ssh.messages.deleteSuccess', {id: id}).toString()
				));
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.ssh.messages.deleteFailed', {id: id}));
	}
}
</script>
