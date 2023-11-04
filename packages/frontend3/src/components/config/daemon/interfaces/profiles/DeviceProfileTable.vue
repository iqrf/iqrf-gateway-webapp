<template>
	<Card>
		<template #title>
			{{ $t(`components.configuration.daemon.interfaces.profiles.${mappingType}`) }}
		</template>
		<template #titleActions>
			<DeviceProfileForm
				:action='FormAction.Add'
				:mapping-type='mappingType ?? MappingType.SPI'
				@saved='getProfiles'
			/>
			<v-btn
				color='white'
				:icon='mdiReload'
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
				<span>
					<v-icon
						color='success'
						size='large'
						class='me-2'
						:icon='mdiCheckboxMarkedOutline'
						@click='applyProfile(item)'
					/>
					<v-tooltip
						activator='parent'
						location='bottom'
					>
						{{ $t('components.configuration.profiles.actions.apply') }}
					</v-tooltip>
				</span>
				<span>
					<DeviceProfileForm
						:action='FormAction.Edit'
						:mapping-type='mappingType ?? MappingType.SPI'
						:device-profile='item'
						@saved='getProfiles'
					/>
					<v-tooltip
						activator='parent'
						location='bottom'
					>
						{{ $t('components.configuration.profiles.actions.edit') }}
					</v-tooltip>
				</span>
				<span>
					<DeviceProfileDeleteDialog
						:profile='item'
						@deleted='getProfiles'
					/>
					<v-tooltip
						activator='parent'
						location='bottom'
					>
						{{ $t('components.configuration.profiles.actions.delete') }}
					</v-tooltip>
				</span>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayDaemonMapping } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { MappingType } from '@iqrf/iqrf-gateway-webapp-client/types/Config/Mapping';
import { mdiCheckboxMarkedOutline, mdiReload } from '@mdi/js';
import {
	onMounted,
	type PropType,
	type Ref,
	ref,
} from 'vue';
import { useI18n } from 'vue-i18n';

import Card from '@/components/Card.vue';
import DeviceProfileDeleteDialog from '@/components/config/daemon/interfaces/profiles/DeviceProfileDeleteDialog.vue';
import DeviceProfileForm from '@/components/config/daemon/interfaces/profiles/DeviceProfileForm.vue';
import DataTable from '@/components/DataTable.vue';
import { FormAction } from '@/enums/controls';
import { useApiClient } from '@/services/ApiClient';
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
	{key: 'name', title: i18n.t('components.configuration.profiles.name')},
	{key: 'type', title: i18n.t('components.configuration.profiles.profileType')},
	{key: 'deviceType', title: i18n.t('components.configuration.profiles.deviceType')},
	{key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false},
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
