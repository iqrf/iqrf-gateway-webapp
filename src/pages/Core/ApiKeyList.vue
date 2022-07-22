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
		<v-card>
			<v-card-text>
				<v-data-table
					:loading='loading'
					:headers='header'
					:items='keys'
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
						{{ item.expiration !== null ? timeString(item) : 'never' }}
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							color='info'
							small
							:to='"/security/api-key/edit/" + item.id'
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn> <ApiKeyDeleteDialog
							:api-key='item'
							@deleted='getKeys'
						/>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import ApiKeyDeleteDialog from '@/components/Core/ApiKeyDeleteDialog.vue';

import ApiKeyService from '@/services/ApiKeyService';
import {DateTime} from 'luxon';
import {extendedErrorToast} from '@/helpers/errorToast';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IApiKey} from '@/interfaces/apiKey';


@Component({
	components: {
		ApiKeyDeleteDialog,
	},
	metaInfo: {
		title: 'core.security.apiKey.title'
	}
})

/**
 * List of existing API keys
 */
export default class ApiKeyList extends Vue {
	/**
	 * @var {boolean} loading Loading visibility
	 */
	private loading = false;

	/**
	 * @var {Array<ApiKey>} keys List of API key objects
	 */
	private keys: Array<IApiKey> = [];

	/**
	 * @var {Array<DataTableHeader>} header Data table header
	 */
	private header: Array<DataTableHeader> = [
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
			sortable: false,
			filterable: false,
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
		if (!item.expiration) {
			return '';
		}
		return DateTime.fromISO(item.expiration).toLocaleString(DateTime.DATETIME_FULL);
	}

}
</script>
