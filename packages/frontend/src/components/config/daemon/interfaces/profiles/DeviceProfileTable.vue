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
	<ICard :width='cardWidth'>
		<template #title>
			{{ $t(`components.config.daemon.interfaces.profiles.${mappingType}`) }}
		</template>
		<template #titleActions>
			<DeviceProfileForm
				:action='Action.Add'
				:disabled='componentState === ComponentState.Reloading'
				:mapping-type='mappingType'
				@saved='getProfiles()'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:disabled='componentState === ComponentState.Action'
				:tooltip='$t("components.config.profiles.actions.reload")'
				@click='getProfiles()'
			/>
		</template>
		<IDataTable
			:headers='headers'
			:items='filteredProfiles'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			:hover='true'
			:dense='true'
			:items-per-page='5'
			:no-data-text='noDataText'
			disable-column-filtering
		>
			<template #item.actions='{ item }'>
				<IDataTableAction
					:action='Action.Apply'
					:tooltip='$t("components.config.profiles.actions.apply")'
					:disabled='componentState === ComponentState.Reloading'
					@click='applyProfile(item)'
				/>
				<DeviceProfileForm
					:action='Action.Edit'
					:mapping-type='mappingType'
					:device-profile='toRaw(item)'
					:disabled='componentState === ComponentState.Reloading'
					@saved='getProfiles()'
				/>
				<DeviceProfileDeleteDialog
					:profile='item'
					:disabled='componentState === ComponentState.Reloading'
					@deleted='getProfiles()'
				/>
			</template>
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayDaemonMapping } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { MappingType } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
	IDataTableAction,
} from '@iqrf/iqrf-vue-ui';
import { computed, onMounted, type PropType, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { useDisplay } from 'vuetify';

import DeviceProfileDeleteDialog from '@/components/config/daemon/interfaces/profiles/DeviceProfileDeleteDialog.vue';
import DeviceProfileForm from '@/components/config/daemon/interfaces/profiles/DeviceProfileForm.vue';
import { useApiClient } from '@/services/ApiClient';

const componentProps = defineProps({
	mappingType: {
		type: String as PropType<MappingType>,
		required: true,
	},
});
const emit = defineEmits(['apply']);
const display = useDisplay();
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

const cardWidth = computed(() => {
	if (display.lgAndUp.value) {
		return '50vw';
	}
	if (display.md.value) {
		return '75vw';
	}
	return '100vw';
});


const filteredProfiles = computed(() => {
	return profiles.value.filter((item: IqrfGatewayDaemonMapping) => item.type === componentProps.mappingType);
});

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return 'components.config.profiles.noData.fetchError';
	}
	return 'components.config.profiles.noData.empty';
});

async function getProfiles(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
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
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.profiles.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

function applyProfile(profile: IqrfGatewayDaemonMapping): void {
	emit('apply', profile);
}

onMounted(() => {
	getProfiles();
});
</script>
