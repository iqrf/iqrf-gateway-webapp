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
			{{ $t('components.config.profiles.title') }}
		</template>
		<template #titleActions>
			<DeviceProfileForm
				:action='Action.Add'
				@saved='getProfiles()'
			/>
			<CardTitleActionBtn
				:action='Action.Reload'
				@click='getProfiles()'
			/>
		</template>
		<DataTable
			:headers='headers'
			:items='profiles'
			:loading='componentState === ComponentState.Loading'
			:hover='true'
			:dense='true'
			:items-per-page='5'
		>
			<template #item.actions='{ item }'>
				<DataTableAction
					:action='Action.Apply'
					:tooltip='$t("components.config.profiles.actions.apply")'
					@click='applyProfile(item)'
				/>
				<DeviceProfileForm
					:action='Action.Edit'
					:device-profile='item'
					@saved='getProfiles()'
				/>
				<DeviceProfileDeleteDialog
					:profile='item'
					@deleted='getProfiles()'
				/>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type IqrfGatewayControllerService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayControllerMapping } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import DeviceProfileDeleteDialog from '@/components/config/controller/profiles/DeviceProfileDeleteDialog.vue';
import DeviceProfileForm from '@/components/config/controller/profiles/DeviceProfileForm.vue';
import Card from '@/components/layout/card/Card.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const emit = defineEmits(['apply']);
const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayControllerService = useApiClient().getConfigServices().getIqrfGatewayControllerService();
const profiles: Ref<IqrfGatewayControllerMapping[]> = ref([]);
const headers = [
	{ key: 'name', title: i18n.t('components.config.profiles.name') },
	{ key: 'deviceType', title: i18n.t('components.config.profiles.deviceType') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];

async function getProfiles(): Promise<void> {
	componentState.value = ComponentState.Loading;
	try {
		profiles.value = (await service.listMappings()).sort((a, b) => {
			if (a === b) {
				return 0;
			}
			if (a.deviceType === b.deviceType) {
				return a.name.localeCompare(b.name);
			}
			return a.deviceType.localeCompare(b.deviceType);
		});
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error('TODO FETCH ERROR HANDLING');
		componentState.value = ComponentState.FetchFailed;
	}
}

function applyProfile(profile: IqrfGatewayControllerMapping): void {
	emit('apply', profile);
}

onMounted(() => {
	getProfiles();
});
</script>
