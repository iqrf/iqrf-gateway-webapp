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
			{{ $t(`components.config.daemon.interfaces.profiles.${mappingType}`) }}
		</template>
		<template #titleActions>
			<DeviceProfileForm
				:action='Action.Add'
				:mapping-type='mappingType ?? MappingType.SPI'
				@saved='getProfiles()'
			/>
			<ICardTitleActionBtn
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
					:mapping-type='mappingType ?? MappingType.SPI'
					:device-profile='item'
					@saved='getProfiles()'
				/>
				<DeviceProfileDeleteDialog
					:profile='item'
					@deleted='getProfiles()'
				/>
			</template>
		</DataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayDaemonMapping } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { MappingType } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { Action, ICard, ICardTitleActionBtn } from '@iqrf/iqrf-vue-ui';
import { onMounted, type PropType, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import DeviceProfileDeleteDialog from '@/components/config/daemon/interfaces/profiles/DeviceProfileDeleteDialog.vue';
import DeviceProfileForm from '@/components/config/daemon/interfaces/profiles/DeviceProfileForm.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

defineProps({
	mappingType: {
		type: [String, null] as PropType<MappingType | null>,
		default: null,
		required: false,
	},
});
const emit = defineEmits(['apply']);
const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const headers = [
	{ key: 'name', title: i18n.t('components.config.profiles.name') },
	{ key: 'type', title: i18n.t('components.config.profiles.profileType') },
	{ key: 'deviceType', title: i18n.t('components.config.profiles.deviceType') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const profiles: Ref<IqrfGatewayDaemonMapping[]> = ref([]);

async function getProfiles(): Promise<void> {
	componentState.value = ComponentState.Loading;
	try {
		profiles.value = (await service.listMappings()).sort((a, b): number => {
			if (a === b) {
				return 0;
			}
			if (a.deviceType === b.deviceType) {
				return a.name.localeCompare(b.name);
			}
			return a.deviceType.localeCompare(b.deviceType);
		});
	} catch {
		//
	}
	componentState.value = ComponentState.Ready;
}

function applyProfile(profile: IqrfGatewayDaemonMapping): void {
	emit('apply', profile);
}

onMounted(() => {
	getProfiles();
});
</script>
