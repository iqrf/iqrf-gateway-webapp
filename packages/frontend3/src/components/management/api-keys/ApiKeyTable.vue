<template>
	<Card>
		<template #title>
			{{ $t('pages.management.apiKeys.title') }}
		</template>
		<template #titleActions>
			<ApiKeyForm :action='FormAction.Add' @refresh='getKeys' />
			<v-btn
				color='white'
				:icon='mdiReload'
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
			<template #item.expiration='{ item }'>
				{{ item.expiration.setLocale(localeStore.getLocale).toLocaleString(DateTime.DATETIME_FULL_WITH_SECONDS) }}
			</template>
			<template #item.actions='{ item }'>
				<span>
					<ApiKeyForm
						:action='FormAction.Edit'
						:api-key='toRaw(item)'
						@refresh='getKeys'
					/>
				</span>
				<span>
					<ApiKeyDeleteDialog
						:api-key='toRaw(item)'
						@refresh='getKeys'
					/>
				</span>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { onMounted, ref, Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { useLocaleStore } from '@/store/locale';

import ApiKeyDeleteDialog from '@/components/management/api-keys/ApiKeyDeleteDialog.vue';
import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';

import { useApiClient } from '@/services/ApiClient';
import { ApiKeyService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { ApiKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiReload } from '@mdi/js';
import { DateTime } from 'luxon';
import ApiKeyForm from './ApiKeyForm.vue';
import { FormAction } from '@/enums/controls';

const i18n = useI18n();
const localeStore = useLocaleStore();
const service: ApiKeyService = useApiClient().getApiKeyService();
const loading: Ref<boolean> = ref(false);
const headers = [
	{key: 'id', title: i18n.t('common.columns.id').toString()},
	{key: 'description', title: i18n.t('common.columns.description').toString()},
	{key: 'expiration', title: i18n.t('components.management.apiKeys.table.expiration').toString()},
	{key: 'actions', title: i18n.t('common.columns.actions').toString(), align: 'end'},
];
const keys: Ref<ApiKeyInfo[]> = ref([]);

onMounted(() => {
	getKeys();
});

function getKeys(): void {
	loading.value = true;
	service.list()
		.then((data: ApiKeyInfo[]) => {
			keys.value = data;
			loading.value = false;
		})
		.catch(() => loading.value = false);
}

</script>
