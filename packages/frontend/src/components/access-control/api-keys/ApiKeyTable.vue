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
	<ICard>
		<template #title>
			{{ $t('pages.accessControl.apiKeys.title') }}
		</template>
		<template #titleActions>
			<ApiKeyForm
				:action='Action.Add'
				:disabled='componentState === ComponentState.Reloading'
				@refresh='getKeys()'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:disabled='componentState === ComponentState.Action'
				:tooltip='$t("components.accessControl.apiKeys.actions.reload")'
				@click='getKeys()'
			/>
		</template>
		<IDataTable
			:headers='headers'
			:items='keys'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			:no-data-text='noDataText'
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
					:disabled='componentState === ComponentState.Reloading'
					@refresh='getKeys()'
				/>
				<ApiKeyDeleteDialog
					:api-key='toRaw(item)'
					:disabled='componentState === ComponentState.Reloading'
					@refresh='getKeys()'
				/>
			</template>
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { type ApiKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import { type ApiKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
} from '@iqrf/iqrf-vue-ui';
import { DateTime } from 'luxon';
import { computed, onMounted, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import ApiKeyDeleteDialog from '@/components/access-control/api-keys/ApiKeyDeleteDialog.vue';
import ApiKeyForm from '@/components/access-control/api-keys/ApiKeyForm.vue';
import { useApiClient } from '@/services/ApiClient';
import { useLocaleStore } from '@/store/locale';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const localeStore = useLocaleStore();
const service: ApiKeyService = useApiClient().getSecurityServices().getApiKeyService();
const headers = computed(() => [
	{ key: 'id', title: i18n.t('common.columns.id') },
	{ key: 'description', title: i18n.t('common.columns.description') },
	{ key: 'expiration', title: i18n.t('components.accessControl.apiKeys.expiration') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
]);
const keys: Ref<ApiKeyInfo[]> = ref([]);

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return 'components.accessControl.apiKeys.noData.fetchError';
	}
	return 'components.accessControl.apiKeys.noData.empty';
});

async function getKeys(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		keys.value = await service.list();
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.accessControl.apiKeys.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

function formatTime(time: DateTime | null): string|null {
	if (time === null) {
		return null;
	}
	return time.setLocale(localeStore.getLocale).toLocaleString(DateTime.DATETIME_FULL_WITH_SECONDS);
}

onMounted(() => {
	getKeys();
});
</script>
