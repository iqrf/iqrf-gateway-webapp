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
	<Card>
		<template #title>
			{{ $t('pages.accessControl.apiKeys.title') }}
		</template>
		<template #titleActions>
			<ApiKeyForm :action='Action.Add' @refresh='getKeys' />
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
			<template #item.expiration='{ item }'>
				{{ formatTime(item.expiration) }}
			</template>
			<template #item.actions='{ item }'>
				<ApiKeyForm
					:action='Action.Edit'
					:api-key='toRaw(item)'
					@refresh='getKeys'
				/>
				<ApiKeyDeleteDialog
					:api-key='toRaw(item)'
					@refresh='getKeys'
				/>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type ApiKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import { type ApiKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { DateTime } from 'luxon';
import { onMounted, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';

import ApiKeyDeleteDialog from '@/components/access-control/api-keys/ApiKeyDeleteDialog.vue';
import ApiKeyForm from '@/components/access-control/api-keys/ApiKeyForm.vue';
import Card from '@/components/layout/card/Card.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import { useApiClient } from '@/services/ApiClient';
import { useLocaleStore } from '@/store/locale';
import { Action } from '@/types/Action';

const i18n = useI18n();
const localeStore = useLocaleStore();
const service: ApiKeyService = useApiClient().getSecurityServices().getApiKeyService();
const loading: Ref<boolean> = ref(false);
const headers = [
	{ key: 'id', title: i18n.t('common.columns.id') },
	{ key: 'description', title: i18n.t('common.columns.description') },
	{ key: 'expiration', title: i18n.t('components.accessControl.apiKeys.table.expiration') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
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
