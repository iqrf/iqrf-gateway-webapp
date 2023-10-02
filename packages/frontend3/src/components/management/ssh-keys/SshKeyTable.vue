<template>
	<Card>
		<DataTable
			:headers='headers'
			:items='keys'
			:expanded='expanded'
			:loading='loading'
			:hover='true'
			:dense='true'
		>
			<template #top>
				<v-toolbar
					color='primary'
					density='compact'
					rounded>
					<v-toolbar-title>{{ $t('pages.management.sshKeys.title') }}</v-toolbar-title>
					<v-toolbar-items>
						<v-btn
							color='white'
							:icon='mdiPlus'
							to='/management/ssh-keys/add'
						/>
						<v-btn
							color='white'
							:icon='mdiReload'
							@click='getKeys'
						/>
					</v-toolbar-items>
				</v-toolbar>
			</template>
			<template #item.createdAt='{ item }'>
				{{ item.createdAt.setLocale(localeStore.getLocale).toLocaleString(DateTime.DATETIME_FULL_WITH_SECONDS) }}
			</template>
			<template #item.actions='{ item, internalItem, toggleExpand }'>
				<span>
					<v-icon
						color='primary'
						:icon='mdiInformation'
						@click='toggleExpand(internalItem)'
					/>
				</span>
				<span>
					<SshKeyDeleteDialog :ssh-key='toRaw(item)' @refresh='getKeys' />
				</span>
			</template>
			<template #expanded-row='{ columns, item }'>
				<td :colspan='columns.length'>
					<v-table
						density='compact'
						hover
					>
						<tbody>
							<tr>
								<th>{{ $t('components.sshKeys.table.type') }}</th>
								<td>
									{{ item.type }}
								</td>
							</tr>
							<tr>
								<th>{{ $t('components.sshKeys.table.hash') }}</th>
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
								<th>{{ $t('components.sshKeys.table.key') }}</th>
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
import { onMounted, ref, Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';

import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';

import { useApiClient } from '@/services/ApiClient';
import { SshKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { mdiContentCopy, mdiInformation, mdiPlus, mdiReload } from '@mdi/js';
import SshKeyDeleteDialog from '@/components/management/ssh-keys/SshKeyDeleteDialog.vue';
import { useLocaleStore } from '@/store/locale';
import { DateTime } from 'luxon';

const i18n = useI18n();
const localeStore = useLocaleStore();
const service = useApiClient().getGatewayServices().getSshKeyService();
const loading: Ref<boolean> = ref(false);
const headers = [
	{key: 'id', title: i18n.t('common.columns.id').toString()},
	{key: 'description', title: i18n.t('common.columns.description').toString()},
	{key: 'createdAt', title: i18n.t('components.sshKeys.table.createdAt').toString()},
	{key: 'actions', title: i18n.t('common.columns.actions').toString(), align: 'end'},
];
const keys: Ref<SshKeyInfo[]> = ref([]);
const expanded: Ref<SshKeyInfo[]> = ref([]);

onMounted(() => {
	getKeys();
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
