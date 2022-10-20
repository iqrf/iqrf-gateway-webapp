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
					:fields='fields'
					:items='keys'
					:striped='true'
					:pagination='true'
					:items-per-page='20'
					:column-filter='true'
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #expiration='{item}'>
						<td v-if='item.expiration !== null'>
							{{ timeString(item) }}
						</td>
						<td v-else>
							never
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='info'
								size='sm'
								:to='"/security/api-key/edit/" + item.id'
							>
								<CIcon :content='cilPencil' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								color='danger'
								size='sm'
								@click='deleteKey = item.id'
							>
								<CIcon :content='cilTrash' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			color='danger'
			:show='deleteKey !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('core.security.apiKey.modal.title') }}
				</h5>
			</template>
			{{ $t('core.security.apiKey.modal.prompt', {key: deleteKey}) }}
			<template #footer>
				<CButton
					color='danger'
					@click='removeKey'
				>
					{{ $t('forms.delete') }}
				</CButton> <CButton
					color='secondary'
					@click='deleteKey = null'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CIcon} from '@coreui/vue/src';

import ApiKeyService from '@/services/ApiKeyService';
import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {DateTime} from 'luxon';
import {extendedErrorToast} from '@/helpers/errorToast';

import {AxiosError, AxiosResponse} from 'axios';
import {IField} from '@/interfaces/coreui';


interface ApiKey {
	description: string
	expiration: string
	id: number
}

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon
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
	 * @var {number|null} deletekey API key id used in remove modal
	 */
	private deleteKey: number|null = null;

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
	 * @var {Array<ApiKey>} keys List of API key objects
	 */
	private keys: Array<ApiKey> = [];

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
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		return ApiKeyService.getApiKeys()
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.keys = response.data;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.security.apiKey.messages.listFetchFailed'));
	}

	/**
	 * Removes an existing API key
	 */
	private removeKey(): void  {
		if (this.deleteKey === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		const key = this.deleteKey;
		this.deleteKey = null;
		ApiKeyService.deleteApiKey(key)
			.then(() => {
				this.getKeys().then(() => {
					this.$toast.success(this.$t('core.security.apiKey.messages.deleteSuccess', {key: key}).toString());
				});
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.security.apiKey.messages.deleteFailed', {key: key}));
	}

	/**
	 * Converts expiration date and time from UTC to locale string
	 * @returns {string} Expiration date and time in locale format
	 */
	private timeString(item: ApiKey): string {
		return DateTime.fromISO(item.expiration).toLocaleString(DateTime.DATETIME_FULL);
	}

}
</script>

<style scoped>
.card-header {
	padding-bottom: 0;
}

</style>
