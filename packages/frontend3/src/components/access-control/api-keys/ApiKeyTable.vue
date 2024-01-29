<template>
	<Card>
		<template #title>
			{{ $t('pages.accessControl.apiKeys.title') }}
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
				{{ formatTime(item.expiration) }}
			</template>
			<template #item.actions='{ item }'>
				<span>
					<ApiKeyForm
						:action='FormAction.Edit'
						:api-key='toRaw(item)'
						@refresh='getKeys'
					/>
					<v-tooltip
						activator='parent'
						location='bottom'
					>
						{{ $t('components.accessControl.apiKeys.actions.edit') }}
					</v-tooltip>
				</span>
				<ApiKeyDeleteDialog
					:api-key='toRaw(item)'
					@refresh='getKeys'
				/>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type ApiKeyService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { type ApiKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiReload } from '@mdi/js';
import { DateTime } from 'luxon';
import { onMounted, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';

import ApiKeyDeleteDialog from '@/components/access-control/api-keys/ApiKeyDeleteDialog.vue';
import ApiKeyForm from '@/components/access-control/api-keys/ApiKeyForm.vue';
import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';
import { FormAction } from '@/enums/controls';
import { useApiClient } from '@/services/ApiClient';
import { useLocaleStore } from '@/store/locale';

const i18n = useI18n();
const localeStore = useLocaleStore();
const service: ApiKeyService = useApiClient().getApiKeyService();
const loading: Ref<boolean> = ref(false);
const headers = [
	{key: 'id', title: i18n.t('common.columns.id')},
	{key: 'description', title: i18n.t('common.columns.description')},
	{key: 'expiration', title: i18n.t('components.accessControl.apiKeys.table.expiration')},
	{key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false},
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

function formatTime(time: DateTime | null): string|null {
	if (time === null) {
		return null;
	}
	return time.setLocale(localeStore.getLocale).toLocaleString(DateTime.DATETIME_FULL_WITH_SECONDS);
}

</script>
