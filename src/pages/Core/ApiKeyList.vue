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
						</v-btn> <v-dialog
							v-model='deleteModal'
							width='50%'
						>
							<template #activator='{on, attrs}'>
								<v-btn
									color='error'
									small
									v-bind='attrs'
									@click='deleteKey = item.id'
									v-on='on'
								>
									<v-icon small>
										mdi-delete
									</v-icon>
									{{ $t('table.actions.delete') }}
								</v-btn>
							</template>
							<v-card>
								<v-card-title>{{ $t('core.security.apiKey.modal.title') }}</v-card-title>
								<v-card-text>{{ $t('core.security.apiKey.modal.prompt', {key: deleteKey}) }}</v-card-text>
								<v-card-actions>
									<v-spacer />
									<v-btn
										color='error'
										@click='removeKey'
									>
										{{ $t('forms.delete') }}
									</v-btn>
									<v-btn
										color='secondary'
										@click='deleteKey = -1'
									>
										{{ $t('forms.cancel') }}
									</v-btn>
								</v-card-actions>
							</v-card>
						</v-dialog>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import ApiKeyService from '@/services/ApiKeyService';
import {DateTime} from 'luxon';
import {extendedErrorToast} from '@/helpers/errorToast';

import {AxiosError, AxiosResponse} from 'axios';
import {DataTableHeader} from 'vuetify';
import { IApiKey } from '@/interfaces/apiKey';

@Component({
	metaInfo: {
		title: 'core.security.apiKey.title'
	}
})

/**
 * List of existing API keys
 */
export default class ApiKeyList extends Vue {

	/**
	 * @var {Array<ApiKey>} keys List of API key objects
	 */
	private keys: Array<IApiKey> = [];

	/**
	 * @var {number} deleteKey API key id used in remove modal
	 */
	private deleteKey = -1;

	/**
	 * @var {boolean} deleteModal Delete modal visibility
	 */
	get deleteModal(): boolean {
		return this.deleteKey !== -1;
	}

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
		this.deleteKey = -1;
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
	private timeString(item: IApiKey): string {
		if (!item.expiration) {
			return '';
		}
		return DateTime.fromISO(item.expiration).toLocaleString(DateTime.DATETIME_FULL);
	}

}
</script>
