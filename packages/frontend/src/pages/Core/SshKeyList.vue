<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
					:headers='headers'
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
							class='mr-1'
							color='info'
							small
							@click='expandItem(item)'
						>
							<v-icon small>
								mdi-information-outline
							</v-icon>
							{{ $t('table.actions.details') }}
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
		<SshKeyDeleteModal
			v-model='keyDeleteModel'
			@deleted='listKeys'
		/>
	</div>
</template>

<script lang='ts'>
import {SshKeyService} from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import {SshKeyInfo} from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import {AxiosError} from 'axios';
import {Component, Vue} from 'vue-property-decorator';
import {DataTableHeader} from 'vuetify';

import SshKeyDeleteModal from '@/components/Core/SshKeyDeleteModal.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		SshKeyDeleteModal,
	},
	metaInfo: {
		title: 'core.security.ssh.title',
	},
})

/**
 * SSH key list component
 */
export default class SshKeyList extends Vue {
	/**
	 * @var {boolean} loading Loading visibility
	 */
	private loading = false;

	/**
	 * @var {Array<SshKeyInfo>} keys List of authorized SSH public keys
	 */
	private keys: Array<SshKeyInfo> = [];

	/**
	 * @var {Array<SshKeyInfo>} expandedKeys List of expanded SSH keys
	 */
	private expandedKeys: Array<SshKeyInfo> = [];

	/**
	 * @var {SshKeyInfo|null} keyDeleteModel SSH key to delete
	 */
	private keyDeleteModel: SshKeyInfo|null = null;

	/**
	 * @constant {Diction<string|boolean>} dateFormat Date formatting options
	 */
	private dateFormat: Record<string, string|boolean> = {
		year: 'numeric',
		month: 'short',
		day: 'numeric',
		hour12: false,
		hour: 'numeric',
		minute: 'numeric',
		second: 'numeric',
	};

	/**
	 * @var {Array<DataTableHeader>} header Data table header
	 */
	private headers: Array<DataTableHeader> = [
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
   * @property {SshKeyService} sshKeyService SSH key service
   */
	private sshKeyService: SshKeyService = useApiClient().getSecurityServices().getSshKeyService();

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
		return this.sshKeyService.list()
			.then((response: SshKeyInfo[]) => {
				this.keys = response;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'core.security.ssh.messages.listFailed');
			});
	}

	/**
	 * Opens delete modal with SSH key
	 * @param {SshKeyInfo} key SSH key
	 */
	private deleteKey(key: SshKeyInfo): void {
		this.keyDeleteModel = key;
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
	 * @var {SshKeyInfo} key SSH key
	 */
	private expandItem(item: SshKeyInfo): void {
		if (this.expandedKeys.includes(item)) {
			this.expandedKeys = this.expandedKeys.filter(key => key.id !== item.id);
		} else {
			this.expandedKeys.push(item);
		}
	}

	/**
	 * Converts expiration date and time from UTC to locale string
	 * @var {SshKeyInfo} key SSH key
	 * @returns {string} Expiration date and time in locale format
	 */
	private timeString(item: SshKeyInfo): string {
		return item.createdAt!.toLocaleString(this.dateFormat);
	}

}
</script>
