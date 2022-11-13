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
		<h1>{{ $t('core.security.apiKey.title') }}</h1>
		<CCard>
			<CCardHeader class='border-0'>
				<CButton
					color='success'
					size='sm'
					class='float-right'
					to='/security/api-key/add'
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
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #expiration='{item}'>
						<td>
							{{ item.expiration === null ? $t('core.security.apiKey.noExpiration') : timeString(item) }}
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								class='mr-1'
								color='info'
								size='sm'
								:to='"/security/api-key/edit/" + item.id'
							>
								<CIcon :content='cilPencil' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								color='danger'
								size='sm'
								@click='showDeleteModal(item)'
							>
								<CIcon :content='cilTrash' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<ApiKeyDeleteModal ref='deleteModal' @deleted='getKeys' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CIcon} from '@coreui/vue/src';
import ApiKeyDeleteModal from '@/components/Core/ApiKeyDeleteModal.vue';

import ApiKeyService from '@/services/ApiKeyService';
import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {DateTime} from 'luxon';
import {extendedErrorToast} from '@/helpers/errorToast';

import {AxiosError, AxiosResponse} from 'axios';
import {IApiKey} from '@/interfaces/Core/ApiKey';
import {IField} from '@/interfaces/Coreui';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon,
		ApiKeyDeleteModal,
	},
	data: () => ({
		cilPencil,
		cilPlus,
		cilTrash,
	}),
	metaInfo: {
		title: 'core.security.apiKey.title'
	}
})

/**
 * List of existing API keys
 */
export default class ApiKeyList extends Vue {
	/**
	 * @var {boolean} loading Indicates that request is in progress
	 */
	private loading = false;

	/**
	 * @constant {Array<IField>} fields Array of CoreUI data table columns
	 */
	private fields: Array<IField> = [
		{
			key: 'id',
			label: this.$t('core.security.apiKey.form.id'),
		},
		{
			key: 'description',
			label: this.$t('core.security.apiKey.form.description'),
		},
		{
			key: 'expiration',
			label: this.$t('core.security.apiKey.form.expiration'),
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			filter: false,
			sorter: false,
		},
	];

	/**
	 * @var {Array<IApiKey>} keys List of API key objects
	 */
	private keys: Array<IApiKey> = [];

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.getKeys();
	}

	/**
	 * Retrieves list of existing API keys
	 */
	private getKeys(): Promise<void> {
		this.loading = true;
		return ApiKeyService.getApiKeys()
			.then((response: AxiosResponse) => {
				this.keys = response.data;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'core.security.apiKey.messages.listFetchFailed');
			});
	}

	/**
	 * Converts expiration date and time from UTC to locale string
	 * @returns {string} Expiration date and time in locale format
	 */
	private timeString(item: IApiKey): string {
		return DateTime.fromISO(item.expiration).toLocaleString(DateTime.DATETIME_FULL);
	}

	/**
	 * Opens delete modal with API key
	 * @param {IApiKey} key API key
	 */
	private showDeleteModal(key: IApiKey) {
		(this.$refs.deleteModal as ApiKeyDeleteModal).showModal(key);
	}
}
</script>
