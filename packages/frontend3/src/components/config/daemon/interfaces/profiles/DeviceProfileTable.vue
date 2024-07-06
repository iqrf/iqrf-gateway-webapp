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
			{{ $t(`components.configuration.daemon.interfaces.profiles.${mappingType}`) }}
		</template>
		<template #titleActions>
			<DeviceProfileForm
				:action='Action.Add'
				:mapping-type='mappingType ?? MappingType.SPI'
				@saved='getProfiles'
			/>
			<CardTitleActionBtn
				:action='Action.Reload'
				@click='getProfiles'
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
					:tooltip='$t("components.configuration.profiles.actions.apply")'
					@click='applyProfile(item)'
				/>
				<DeviceProfileForm
					:action='Action.Edit'
					:mapping-type='mappingType ?? MappingType.SPI'
					:device-profile='item'
					@saved='getProfiles'
				/>
				<DeviceProfileDeleteDialog
					:profile='item'
					@deleted='getProfiles'
				/>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayDaemonMapping } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { MappingType } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { onMounted, type PropType, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import DeviceProfileDeleteDialog from '@/components/config/daemon/interfaces/profiles/DeviceProfileDeleteDialog.vue';
import DeviceProfileForm from '@/components/config/daemon/interfaces/profiles/DeviceProfileForm.vue';
import Card from '@/components/layout/card/Card.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import DataTable from '@/components/layout/data-table/DataTable.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

const componentProps = defineProps({
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
	{ key: 'name', title: i18n.t('components.configuration.profiles.name') },
	{ key: 'type', title: i18n.t('components.configuration.profiles.profileType') },
	{ key: 'deviceType', title: i18n.t('components.configuration.profiles.deviceType') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const profiles: Ref<IqrfGatewayDaemonMapping[]> = ref([]);

async function getProfiles(): Promise<void> {
	componentState.value = ComponentState.Loading;
	service.listMappings(componentProps.mappingType)
		.then((data: IqrfGatewayDaemonMapping[]) => {
			profiles.value = data.sort((a: IqrfGatewayDaemonMapping, b: IqrfGatewayDaemonMapping): number => {
				if (a === b) {
					return 0;
				}
				if (a.deviceType === b.deviceType) {
					return a.name.localeCompare(b.name);
				}
				return a.deviceType.localeCompare(b.deviceType);
			});
			componentState.value = ComponentState.Ready;
		});
}

function applyProfile(profile: IqrfGatewayDaemonMapping): void {
	emit('apply', profile);
}

onMounted(() => {
	getProfiles();
});
</script>
