<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		<v-card>
			<v-card-text>
				<v-data-table
					:loading='loading'
					:headers='headers'
					:items='keys'
					:no-data-text='$t("table.messages.noRecords")'
				>
					<template #top>
						<v-toolbar dense flat>
							<v-spacer />
							<v-btn
								color='success'
								small
								to='/security/api-key/add'
							>
								<v-icon small>
									mdi-plus
								</v-icon>
								{{ $t('table.actions.add') }}
							</v-btn>
						</v-toolbar>
					</template>
					<template #[`item.expiration`]='{item}'>
						{{ item.expiration !== null ? timeString(item) : $t('core.security.apiKey.noExpiration') }}
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							class='mr-1'
							color='info'
							small
							:to='"/security/api-key/edit/" + item.id'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn>
						<v-btn
							color='error'
							small
							@click='deleteKey(item)'
						>
							<v-icon small>
								mdi-delete
							</v-icon>
							{{ $t('table.actions.delete') }}
						</v-btn>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
		<ApiKeyDeleteModal
			v-model='keyDeleteModel'
			@deleted='getKeys'
		/>
	</div>
</template>

<script lang='ts'>
import {ApiKeyInfo} from '@iqrf/iqrf-gateway-webapp-client/types';
import {AxiosError} from 'axios';
import {DateTime} from 'luxon';
import {Component, Vue} from 'vue-property-decorator';
import {DataTableHeader} from 'vuetify';

import ApiKeyDeleteModal from '@/components/Core/ApiKeyDeleteModal.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		ApiKeyDeleteModal,
	},
	data: () => ({
		DATETIME_FULL: DateTime.DATETIME_FULL,
	}),
	metaInfo: {
		title: 'core.security.apiKey.title',
	},
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
	 * @var {ApiKeyInfo[]} keys List of API key objects
	 */
	private keys: ApiKeyInfo[] = [];

	/**
	 * @var {ApiKeyInfo|null} keyDeleteModel Key to delete
	 */
	private keyDeleteModel: ApiKeyInfo|null = null;

	/**
	 * @constant {Array<DataTableHeader>} headers Data table headers
	 */
	private headers: Array<DataTableHeader> = [
		{
			value: 'id',
			text: this.$t('core.security.apiKey.form.id').toString(),
		},
		{
			value: 'description',
			text: this.$t('core.security.apiKey.form.description').toString(),
		},
		{
			value: 'expiration',
			text: this.$t('core.security.apiKey.form.expiration').toString(),
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			filterable: false,
			sortable: false,
			align: 'end',
		},
	];

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
		return useApiClient().getApiKeyService().list()
			.then((response: ApiKeyInfo[]) => {
				this.keys = response;
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
	private timeString(item: ApiKeyInfo): string {
		return item.expiration.toLocaleString(DateTime.DATETIME_FULL);
	}

	/**
	 * Opens delete modal with API key
	 * @param {ApiKeyInfo} key API key
	 */
	private deleteKey(key: ApiKeyInfo) {
		this.keyDeleteModel = key;
	}
}
</script>
