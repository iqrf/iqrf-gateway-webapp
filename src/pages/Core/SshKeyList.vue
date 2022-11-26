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
		<h1>{{ $t('core.security.ssh.title') }}</h1>
		<CCard>
			<CCardHeader class='border-0'>
				<CButton
					class='float-right'
					color='success'
					size='sm'
					to='/security/ssh-key/add/'
				>
					<CIcon :content='cilPlus' size='sm' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:loading='loading'
					:fields='fields'
					:items='keys'
					:pagination='true'
					:items-per-page='20'
					:column-filter='true'
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
								color='danger'
								size='sm'
								@click='deleteKey(item)'
							>
								<CIcon :content='cilTrash' size='sm' />
								<span class='d-none d-lg-inline'>
									{{ $t('table.actions.delete') }}
								</span>
							</CButton>
						</td>
					</template>
					<template #show_details='{item}'>
						<td class='py-2'>
							<CButton
								color='info'
								size='sm'
								@click='item.showDetails = !item.showDetails'
							>
								<CIcon :content='cilInfo' />
							</CButton>
						</td>
					</template>
					<template #details='{item}'>
						<CCollapse :show='item.showDetails'>
							<CCardBody>
								<div class='datatable-expansion-table'>
									<table>
										<caption>
											<b>{{ $t('core.security.ssh.table.details') }}</b>
										</caption>
										<tr>
											<th>{{ $t('core.security.ssh.table.type') }}</th>
											<td>{{ item.type }}</td>
										</tr>
										<tr>
											<th style='vertical-align: middle;'>
												{{ $t('core.security.ssh.table.hash') }}
											</th>
											<td style='vertical-align: middle;'>
												{{ item.hash }}
											</td>
											<td>
												<CButton
													v-clipboard:copy='item.hash'
													v-clipboard:success='hashClipboardMessage'
													color='primary'
													size='sm'
												>
													<CIcon :content='cilClipboard' size='sm' />
													<span class='d-none d-lg-inline'>
														{{ $t('forms.clipboardCopy') }}
													</span>
												</CButton>
											</td>
										</tr>
										<tr>
											<th style='vertical-align: middle;'>
												{{ $t('core.security.ssh.table.key') }}
											</th>
											<td style='vertical-align: middle;'>
												{{ item.key }}
											</td>
											<td>
												<CButton
													v-clipboard:copy='item.key'
													v-clipboard:success='keyClipboardMessage'
													color='primary'
													size='sm'
												>
													<CIcon :content='cilClipboard' size='sm' />
													<span class='d-none d-lg-inline'>
														{{ $t('forms.clipboardCopy') }}
													</span>
												</CButton>
											</td>
										</tr>
									</table>
								</div>
							</CCardBody>
						</CCollapse>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<SshKeyDeleteModal ref='deleteModal' @deleted='listKeys' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CCollapse, CDataTable, CIcon} from '@coreui/vue/src';

import {cilPlus, cilTrash, cilInfo, cilClipboard} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';

import SshService from '@/services/SshService';

import {AxiosResponse, AxiosError} from 'axios';
import {IField} from '@/interfaces/Coreui';
import {ISshKey} from '@/interfaces/Core/SshKey';
import SshKeyDeleteModal from '@/components/Core/SshKeyDeleteModal.vue';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CCollapse,
		CDataTable,
		CIcon,
	},
	data: () => ({
		cilClipboard,
		cilInfo,
		cilPlus,
		cilTrash,
	}),
	metaInfo: {
		title: 'core.security.ssh.title',
	},
})

/**
 * SSH key list component
 */
export default class SshKeyList extends Vue {
	/**
	 * @var {boolean} loading Indicates that request is in progress
	 */
	private loading = false;

	/**
	 * @var {Array<ISshKey>} keys List of authorized SSH public keys
	 */
	private keys: Array<ISshKey> = [];

	/**
	 * @var {ISshKey|null} keyToDelete
	 */
	private keyToDelete: ISshKey|null = null;

	/**
	 * @constant {Array<IField>} fields Array of CoreUI data table columns
	 */
	private fields: Array<IField> = [
		{
			key: 'id',
			label: this.$t('core.security.ssh.table.id').toString(),
		},
		{
			key: 'description',
			label: this.$t('core.security.ssh.form.description').toString(),
		},
		{
			key: 'createdAt',
			label: this.$t('core.security.ssh.table.createdAt').toString(),
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title').toString(),
			sorter: false,
			filter: false,
		},
		{
			key: 'show_details',
			label: '',
			_style: 'width: 1%',
			sorter: false,
			filter: false,
		},
	];

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
		this.loading = true;
		return SshService.listKeys()
			.then((response: AxiosResponse) => {
				const keys: Array<ISshKey> = response.data;
				for (const i in keys) {
					keys[i].showDetails = false;
				}
				this.keys = response.data;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'core.security.ssh.messages.listFailed');
				this.loading = false;
			});
	}

	/**
	 * Opens delete modal with SSH key
	 * @param {ISshKey} key API key
	 */
	private deleteKey(key: ISshKey): void {
		(this.$refs.deleteModal as SshKeyDeleteModal).showModal(key);
	}

	/**
	 * SSH public key clipboard copy message
	 */
	private hashClipboardMessage(): void {
		this.clipboardMessage('hashClipboard');
	}

	/**
	 * SSH key hash clipboard copy message
	 */
	private keyClipboardMessage(): void {
		this.clipboardMessage('keyClipboard');
	}

	/**
	 * Clipboard copy message
	 */
	private clipboardMessage(path: string): void {
		this.$toast.success(
			this.$t(`core.security.ssh.messages.${path}`).toString()
		);
	}

}
</script>
