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
				:action='Action.Add'
				:key-types='types'
				@refresh='getKeys'
			/>
			<CardTitleActionBtn
				:action='Action.Reload'
				@click='getKeys'
			/>
		</template>
		<DataTable
			:headers='headers'
			:items='keys'
			:loading='loading'
			:hover='true'
			:dense='true'
		>
			<template #item.createdAt='{ item }'>
				{{ $d(item.createdAt.toJSDate(), 'long') }}
			</template>
			<template #item.actions='{ item, internalItem, toggleExpand }'>
				<DataTableAction
					color='primary'
					:icon='mdiInformation'
					:tooltip='$t("components.accessControl.sshKeys.actions.edit")'
					@click='toggleExpand(internalItem)'
				/>
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
import { mdiContentCopy, mdiInformation } from '@mdi/js';
import { onMounted, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import SshKeyDeleteDialog from '@/components/access-control/ssh-keys/SshKeyDeleteDialog.vue';
import SshKeyForm from '@/components/access-control/ssh-keys/SshKeyForm.vue';
import Card from '@/components/layout/card/Card.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';

const i18n = useI18n();
const service = useApiClient().getGatewayServices().getSshKeyService();
const loading: Ref<boolean> = ref(false);
const headers = [
	{ key: 'id', title: i18n.t('common.columns.id') },
	{ key: 'description', title: i18n.t('common.columns.description') },
	{ key: 'type', title: i18n.t('components.accessControl.sshKeys.table.type') },
	{ key: 'createdAt', title: i18n.t('components.accessControl.sshKeys.table.createdAt') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const types: Ref<string[]> = ref([]);
const keys: Ref<SshKeyInfo[]> = ref([]);

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
