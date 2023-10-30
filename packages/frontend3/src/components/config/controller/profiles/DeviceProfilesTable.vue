<template>
	<Card>
		<template #title>
			{{ $t('pages.configuration.controller.profiles.title') }}
		</template>
		<template #titleActions>
			<DeviceProfileForm
				:action='FormAction.Add'
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
		>
			<template #item.actions='{ item }'>
				<span v-show='showApply'>
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
						{{ $t('components.configuration.controller.profiles.actions.apply') }}
					</v-tooltip>
				</span>
				<span>
					<DeviceProfileForm
						:action='FormAction.Edit'
						:device-profile='item'
						@saved='getProfiles'
					/>
					<v-tooltip
						activator='parent'
						location='bottom'
					>
						{{ $t('components.configuration.controller.profiles.actions.edit') }}
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
						{{ $t('components.configuration.controller.profiles.actions.delete') }}
					</v-tooltip>
				</span>
			</template>
		</DataTable>
	</Card>
</template>

<script lang='ts' setup>
import { type IqrfGatewayControllerService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayControllerMapping } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiCheckboxMarkedOutline, mdiReload } from '@mdi/js';
import { onMounted, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';


import Card from '@/components/Card.vue';
import DeviceProfileDeleteDialog from '@/components/config/controller/profiles/DeviceProfileDeleteDialog.vue';
import DeviceProfileForm from '@/components/config/controller/profiles/DeviceProfileForm.vue';
import DataTable from '@/components/DataTable.vue';
import { FormAction } from '@/enums/controls';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

defineProps({
	showApply: {
		type: Boolean,
		default: false,
		required: false,
	},
});
const emit = defineEmits(['apply']);
const i18n = useI18n();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: IqrfGatewayControllerService = useApiClient().getConfigServices().getIqrfGatewayControllerService();
const profiles: Ref<IqrfGatewayControllerMapping[]> = ref([]);
const headers = [
	{key: 'name', title: i18n.t('components.configuration.controller.profiles.name')},
	{key: 'deviceType', title: i18n.t('components.configuration.controller.profiles.type')},
	{key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false},
];
async function getProfiles(): Promise<void> {
	componentState.value = ComponentState.Loading;
	service.listMappings()
		.then((data: IqrfGatewayControllerMapping[]) => {
			profiles.value = data.sort((a: IqrfGatewayControllerMapping, b: IqrfGatewayControllerMapping): number => {
				if (a === b) {
					return 0;
				}
				if (a.deviceType === b.deviceType) {
					return a.name.localeCompare(b.name);
				}
				return a.deviceType.localeCompare(b.deviceType);
			});
			componentState.value = ComponentState.Ready;
		})
		.catch(() => {
			toast.error('TODO FETCH ERROR HANDLING');
			componentState.value = ComponentState.FetchFailed;
		});
}

function applyProfile(profile: IqrfGatewayControllerMapping): void {
	emit('apply', profile);
}

onMounted(() => {
	getProfiles();
});
</script>
