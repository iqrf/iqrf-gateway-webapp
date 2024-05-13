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
	<Card>
		<template #title>
			{{ $t('pages.accessControl.sshKeys.title') }}
		</template>
		<template #titleActions>
			<SshKeyForm
				:action='FormAction.Add'
				:key-types='types'
				@refresh='getKeys'
			/>
			<v-btn
				color='white'
				:icon='mdiReload'
				@click='getKeys'
			/>
		</template>
		<DataTable
			:headers='headers'
			:items='keys'
			:expanded='expanded'
			:loading='loading'
			:hover='true'
			:dense='true'
		>
			<template #item.createdAt='{ item }'>
				{{ item.createdAt.setLocale(localeStore.getLocale).toLocaleString(DateTime.DATETIME_FULL_WITH_SECONDS) }}
			</template>
			<template #item.actions='{ item, internalItem, toggleExpand }'>
				<span>
					<v-icon
						color='primary'
						size='large'
						class='me-2'
						:icon='mdiInformation'
						@click='toggleExpand(internalItem)'
					/>
					<v-tooltip
						activator='parent'
						location='bottom'
					>
						{{ $t('components.accessControl.sshKeys.actions.edit') }}
					</v-tooltip>
				</span>
				<SshKeyDeleteDialog :ssh-key='toRaw(item)' @refresh='getKeys' />
			</template>
			<template #expanded-row='{ columns, item }'>
				<td :colspan='columns.length'>
					<v-table
						density='compact'
						hover
					>
						<tbody>
							<tr>
								<th>{{ $t('components.accessControl.sshKeys.table.type') }}</th>
								<td>
									{{ item.type }}
								</td>
							</tr>
							<tr>
								<th>{{ $t('components.accessControl.sshKeys.table.hash') }}</th>
								<td>
									{{ item.hash }}
									<v-icon
										color='primary'
										:icon='mdiContentCopy'
										@click='copyToClipboard(item.hash)'
									/>
								</td>
							</tr>
							<tr>
								<th>{{ $t('components.accessControl.sshKeys.table.key') }}</th>
								<td>
									{{ item.key }}
									<v-icon
										color='primary'
										:icon='mdiContentCopy'
										@click='copyToClipboard(item.key)'
									/>
								</td>
							</tr>
						</tbody>
					</v-table>
				</td>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type SshKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { mdiContentCopy, mdiInformation, mdiReload } from '@mdi/js';
import { DateTime } from 'luxon';
import { onMounted, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import SshKeyDeleteDialog from '@/components/access-control/ssh-keys/SshKeyDeleteDialog.vue';
import SshKeyForm from '@/components/access-control/ssh-keys/SshKeyForm.vue';
import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';
import { FormAction } from '@/enums/controls';
import { useApiClient } from '@/services/ApiClient';
import { useLocaleStore } from '@/store/locale';


const i18n = useI18n();
const localeStore = useLocaleStore();
const service = useApiClient().getGatewayServices().getSshKeyService();
const loading: Ref<boolean> = ref(false);
const headers = [
	{ key: 'id', title: i18n.t('common.columns.id') },
	{ key: 'description', title: i18n.t('common.columns.description') },
	{ key: 'createdAt', title: i18n.t('components.accessControl.sshKeys.table.createdAt') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const types: Ref<string[]> = ref([]);
const keys: Ref<SshKeyInfo[]> = ref([]);
const expanded: Ref<SshKeyInfo[]> = ref([]);

onMounted(() => {
	getKeys();
	getKeyTypes();
});

function getKeys(): void {
	loading.value = true;
	service.list()
		.then((rsp: SshKeyInfo[]) => {
			keys.value = rsp;
			loading.value = false;
		})
		.catch(() => loading.value = false);
}

function getKeyTypes(): void {
	service.fetchKeyTypes()
		.then((rsp: string[]) => {
			types.value = rsp;
		})
		.catch(() => toast.error('TODO ERROR HANDLING'));
}

function copyToClipboard(content: string): void {
	navigator.clipboard.writeText(content);
}

</script>

<style lang='scss' scoped>
tbody {
	tr {
		th {
			font-weight: bold !important;
		}
	}
}
</style>
