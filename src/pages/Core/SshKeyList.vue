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
		<v-card>
			<v-card-text>
				<v-data-table
					:headers='header'
					:items='keys'
					:expanded='expandedKeys'
				>
					<template #top>
						<v-toolbar dense flat>
							<v-spacer />
							<v-btn
								color='success'
								small
								to='/security/ssh-key/add'
							>
								<v-icon small>
									mdi-plus
								</v-icon>
								{{ $t('table.actions.add') }}
							</v-btn>
						</v-toolbar>
					</template>
					<template #expanded-item='{headers, item}'>
						<td :colspan='headers.length' class='pl-0 pr-0'>
							<v-simple-table>
								<tbody>
									<tr>
										<th>{{ $t('core.security.ssh.table.type') }}</th>
										<td colspan='2'>
											{{ item.type }}
										</td>
									</tr>
									<tr>
										<th>
											{{ $t('core.security.ssh.table.hash') }}
										</th>
										<td>{{ item.hash }}</td>
										<td class='text-end'>
											<v-btn
												v-clipboard:copy='item.hash'
												v-clipboard:success='hashClipboardMessage'
												color='primary'
												small
											>
												<v-icon small>
													mdi-clipboard-outline
												</v-icon>
												{{ $t('forms.clipboardCopy') }}
											</v-btn>
										</td>
									</tr>
									<tr>
										<th>
											{{ $t('core.security.ssh.table.key') }}
										</th>
										<td style='max-width: 60vw;'>
											{{ item.key }}
										</td>
										<td class='text-end'>
											<v-btn
												v-clipboard:copy='item.key'
												v-clipboard:success='keyClipboardMessage'
												color='primary'
												small
											>
												<v-icon small>
													mdi-clipboard-outline
												</v-icon>
												{{ $t('forms.clipboardCopy') }}
											</v-btn>
										</td>
									</tr>
								</tbody>
							</v-simple-table>
						</td>
					</template>
					<template #[`item.createdAt`]='{item}'>
						{{ timeString(item) }}
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							color='info'
							small
							@click='expandItem(item)'
						>
							<v-icon small>
								mdi-information-outline
							</v-icon>
							{{ $t('table.actions.details') }}
						</v-btn>
						<v-dialog v-model='deleteModal' width='50%'>
							<template #activator='{ on, attrs }'>
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
								<v-card-title>{{ $t('core.security.ssh.modal.title') }}</v-card-title>
								<v-card-text>{{ $t('core.security.ssh.modal.prompt', {id: deleteKey}) }}</v-card-text>
								<v-card-actions>
									<v-btn
										color='error'
										@click='removeKey'
									>
										{{ $t('forms.delete') }}
									</v-btn>
									<v-spacer />
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

import {extendedErrorToast} from '@/helpers/errorToast';

import SshService from '@/services/SshService';

import {AxiosResponse, AxiosError} from 'axios';
import {ISshKey} from '@/interfaces/ssh';
import {DateTime} from 'luxon';
import {DataTableHeader} from 'vuetify';

@Component({
	metaInfo: {
		title: 'core.security.ssh.title',
	},
})

/**
 * SSH key list component
 */
export default class SshKeyList extends Vue {

	/**
	 * @var {Array<ISshKey>} keys List of authorized SSH public keys
	 */
	private keys: Array<ISshKey> = [];

	/**
	 * @var {Array<ISshKey>} expandedKeys List of expanded SSH keys
   */
	private expandedKeys: Array<ISshKey> = [];

	/**
	 * @var {number|null} deleteKey API key id used in remove modal
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
			text: this.$t('core.security.ssh.table.id').toString(),
		},
		{
			value: 'description',
			text: this.$t('core.security.ssh.form.description').toString(),
		},
		{
			value: 'createdAt',
			text: this.$t('core.security.ssh.table.createdAt').toString(),
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
				const keys: Array<ISshKey> = response.data;
				for (const i in keys) {
					keys[i].showDetails = false;
				}
				this.keys = response.data;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.security.ssh.messages.listFailed'));
	}

	/**
	 * Removes authorized SSH public key
	 */
	private removeKey(): void {
		this.$store.commit('spinner/SHOW');
		const id = this.deleteKey;
		this.deleteKey = -1;
		SshService.deleteKey(id)
			.then(() => {
				this.listKeys().then(() => this.$toast.success(
					this.$t('core.security.ssh.messages.deleteSuccess', {id: id}).toString()
				));
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.security.ssh.messages.deleteFailed', {id: id}));
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

	/**
	 * Expands SSH key details
	 * @var {ISshKey} key SSH key
	 */
	private expandItem(item: ISshKey): void {
		if (this.expandedKeys.includes(item)) {
			this.expandedKeys = this.expandedKeys.filter(key => key.id !== item.id);
		} else {
			this.expandedKeys.push(item);
		}
	}

	/**
	 * Converts expiration date and time from UTC to locale string
   * @var {ISshKey} key SSH key
	 * @returns {string} Expiration date and time in locale format
	 */
	private timeString(item: ISshKey): string {
		return DateTime.fromISO(item.createdAt).toLocaleString(DateTime.DATETIME_FULL);
	}

}
</script>
