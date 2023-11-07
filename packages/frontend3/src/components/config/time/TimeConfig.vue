<template>
	<Card>
		<template #title>
			{{ $t('pages.configuration.time.title') }}
		</template>
		<template #titleActions>
			<v-btn
				color='white'
				:icon='mdiReload'
				@click='getTime'
			/>
		</template>
		<v-skeleton-loader
			class='input-skeleton-loader'
			:loading='componentState === ComponentState.Loading'
			type='heading@2, text, table-heading, table-row-divider@2, table-row'
		>
			<v-responsive>
				<v-form ref='form'>
					<v-alert
						class='mb-4'
						type='info'
						variant='tonal'
					>
						{{ $t('components.configuration.time.gatewayDateTime') }} {{ gatewayTime?.formattedTime }} {{ gatewayTime?.abbrevation }} (UTC{{ gatewayTime?.gmtOffset }})
					</v-alert>
					<v-autocomplete
						v-model='timezone'
						:items='timezones'
						:label='$t("components.configuration.time.timezone")'
						item-value='name'
						:item-props='(e) => itemProps(e as Timezone)'
						return-object
					/>
					<v-checkbox
						v-model='timeSet.ntpSync'
						:label='$t("components.configuration.time.ntpSync")'
						density='compact'
					/>
					<DataTable
						v-if='timeSet.ntpSync'
						:headers='headers'
						:items='timeSet.ntpServers'
						:hover='true'
						:dense='true'
						no-data-text='components.configuration.time.ntpServers.noRecords'
					>
						<template #top>
							<v-toolbar color='primary' density='compact' rounded>
								<v-toolbar-title>
									{{ $t('components.configuration.time.ntpServers.title') }}
								</v-toolbar-title>
								<v-toolbar-items>
									<NtpServerForm
										:action='FormAction.Add'
										@save='saveServer'
									/>
									<v-btn
										color='white'
										:icon='mdiDelete'
										@click='clearServers'
									/>
								</v-toolbar-items>
							</v-toolbar>
						</template>
						<template #item.server='{ item }'>
							{{ item }}
						</template>
						<template #item.actions='{ item, index }'>
							<NtpServerForm
								:action='FormAction.Edit'
								:index='index'
								:server='item'
								@save='saveServer'
							/>
							<v-icon
								color='error'
								size='large'
								@click='removeServer(index)'
							>
								{{ mdiDelete }}
							</v-icon>
						</template>
					</DataTable>
					<div v-else>
						<label for='datetimeinput'>
							{{ $t('components.configuration.time.datetime') }}
						</label>
						<VueDatePicker
							id='datetimeinput'
							v-model='datetime'
							:enable-seconds='true'
							:show-now-button='true'
							:teleport='true'
							:state='datePickerState'
						/>
					</div>
				</v-form>
			</v-responsive>
		</v-skeleton-loader>
		<template #actions>
			<v-btn
				color='primary'
				variant='elevated'
				:disabled='componentState === ComponentState.Loading'
				@click='onSubmit'
			>
				{{ $t('common.buttons.save') }}
			</v-btn>
		</template>
	</Card>
</template>

<script lang='ts' setup>
import { type TimeService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway/TimeService';
import { type TimeConfig, type TimeSet, type Timezone } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { mdiDelete, mdiReload } from '@mdi/js';
import { DateTime } from 'luxon';
import { onMounted } from 'vue';
import { type Ref, computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import NtpServerForm from '@/components/config/time/NtpServerForm.vue';
import DataTable from '@/components/DataTable.vue';
import { FormAction } from '@/enums/controls';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: TimeService = useApiClient().getGatewayServices().getTimeService();
const gatewayTime: Ref<TimeConfig | null> = ref(null);
const timezones: Ref<Timezone[]> = ref([]);
const timezone: Ref<Timezone> = ref({
	name: '',
	code: '',
	offset: '',
});
const timeSet: Ref<TimeSet> = ref({
	ntpSync: true,
	ntpServers: [],
});
const datetime = ref(new Date(0));
const headers = [
	{key: 'server', title: i18n.t('components.configuration.time.ntpServers.address')},
	{key: 'actions', title: i18n.t('common.columns.actions'), align: 'end'},
];

async function getTime(): Promise<void> {
	componentState.value = ComponentState.Loading;
	service.getTime()
		.then((data: TimeConfig) => {
			gatewayTime.value = data;
			timezone.value = {
				name: gatewayTime.value.zoneName,
				code: gatewayTime.value.abbrevation,
				offset: gatewayTime.value.gmtOffset,
			};
			timeSet.value = {
				ntpSync: gatewayTime.value.ntpSync,
				ntpServers: gatewayTime.value.ntpServers,
				zoneName: gatewayTime.value.zoneName,
			};
			datetime.value = datetime.value = DateTime.fromSeconds(gatewayTime.value.utcTimestamp, {zone: gatewayTime.value.zoneName}).toJSDate();
			componentState.value = ComponentState.Ready;
		})
		.catch(() => {
			componentState.value = ComponentState.FetchFailed;
			toast.error('TODO ERROR HANDLING');
		});
}

async function onSubmit(): Promise<void> {
	const params: TimeSet = JSON.parse(JSON.stringify(timeSet.value));
	params.zoneName = timezone.value.name;
	if (params.ntpSync) {
		delete params.datetime;
	} else {
		delete params.ntpServers;
		const luxonDate = DateTime.fromJSDate(datetime.value, {zone: (timezone.value.name)});
		params.datetime = luxonDate.toISO()!;
	}
	service.setTime(params)
		.then(() => {
			getTime().then(() => {
				toast.success(
					i18n.t('components.configuration.time.messages.save.success'),
				);
			});
		})
		.catch(() => toast.error('TODO ERROR HANDLING'));
}

async function getTimezones(): Promise<void> {
	service.getTimezones()
		.then((data: Timezone[]) => timezones.value = data);
}

function itemProps(item: Timezone) {
	return {
		title: `(UTC${item.offset}) ${item.name} (${item.code})`,
		subtitle: '',
	};
}

function saveServer(index: number|undefined, server: string) {
	if (index === undefined) {
		if (timeSet.value.ntpServers !== undefined) {
			timeSet.value.ntpServers.push(server);
		}
	} else {
		if (timeSet.value.ntpServers !== undefined) {
			timeSet.value.ntpServers[index] = server;
		}
	}
}

function removeServer(index: number): void {
	timeSet.value.ntpServers?.splice(index, 1);
}


function clearServers(): void {
	if (timeSet.value.ntpServers !== null) {
		timeSet.value.ntpServers = [];
	}
}

const datePickerState = computed((): false|null => {
	if (timeSet.value.ntpSync) {
		return null;
	}
	if (datetime.value !== null) {
		return null;
	}
	return false;
});


onMounted(async () => {
	await getTime();
	await getTimezones();
});
</script>
