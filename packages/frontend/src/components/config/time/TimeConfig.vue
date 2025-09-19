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
			{{ $t('pages.config.time.title') }}
		</template>
		<template #titleActions>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				@click='getTime'
			/>
		</template>
		<v-skeleton-loader
			class='input-skeleton-loader'
			:loading='componentState === ComponentState.Loading'
			type='heading@2, text, table-heading, table-row-divider@2, table-row'
		>
			<v-responsive>
				<v-form ref='form' @submit.prevent='onSubmit'>
					<v-alert
						class='mb-4'
						type='info'
						variant='tonal'
					>
						{{ $t('components.config.time.gatewayDateTime') }} {{ gatewayTime?.formattedTime }} {{ gatewayTime?.abbrevation }} {{ `(UTC${gatewayTime?.gmtOffset})` }}
					</v-alert>
					<v-autocomplete
						v-model='timezone'
						:items='timezones'
						:label='$t("components.config.time.timezone")'
						item-value='name'
						:item-props='(e: Timezone) => itemProps(e)'
						:prepend-inner-icon='mdiMapClock'
						return-object
					/>
					<v-checkbox
						v-model='timeSet.ntpSync'
						:label='$t("components.config.time.ntpSync")'
						:hide-details='false'
					/>
					<IDataTable
						v-if='timeSet.ntpSync'
						:headers='headers'
						:items='timeSet.ntpServers ?? []'
						:hover='true'
						:dense='true'
						no-data-text='components.config.time.ntpServers.noRecords'
					>
						<template #top>
							<v-toolbar color='gray' density='compact' rounded>
								<v-toolbar-title>
									{{ $t('components.config.time.ntpServers.title') }}
								</v-toolbar-title>
								<v-toolbar-items>
									<NtpServerForm
										:action='Action.Add'
										@save='saveServer'
									/>
									<v-btn
										color='red'
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
								:action='Action.Edit'
								:index='index'
								:server='item'
								@save='saveServer'
							/>
							<IDataTableAction
								:action='Action.Delete'
								@click='removeServer(index)'
							/>
						</template>
					</IDataTable>
					<div v-else>
						<label for='datetimeinput'>
							{{ $t('components.config.time.datetime') }}
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
			<IActionBtn
				:action='Action.Edit'
				container-type='card'
				:disabled='componentState === ComponentState.Loading'
				type='submit'
			/>
		</template>
	</ICard>
</template>

<script lang='ts' setup>
import {
	type TimeService,
} from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import {
	type TimeConfig,
	type TimeSet,
	type Timezone,
} from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
	IDataTableAction,
} from '@iqrf/iqrf-vue-ui';
import { mdiDelete, mdiMapClock } from '@mdi/js';
import { DateTime } from 'luxon';
import { computed, onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import NtpServerForm from '@/components/config/time/NtpServerForm.vue';
import { useApiClient } from '@/services/ApiClient';

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
	{ key: 'server', title: i18n.t('components.config.time.ntpServers.address') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end' },
];

async function getTime(): Promise<void> {
	componentState.value = ComponentState.Loading;
	try {
		gatewayTime.value = await service.getTime();
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
		datetime.value = datetime.value = DateTime.fromSeconds(gatewayTime.value.utcTimestamp, { zone: gatewayTime.value.zoneName }).toJSDate();
		componentState.value = ComponentState.Ready;

	} catch {
		componentState.value = ComponentState.FetchFailed;
		toast.error('TODO ERROR HANDLING');
	}
}

async function onSubmit(): Promise<void> {
	const params: TimeSet = structuredClone(timeSet.value);
	params.zoneName = timezone.value.name;
	if (params.ntpSync) {
		delete params.datetime;
	} else {
		delete params.ntpServers;
		const luxonDate = DateTime.fromJSDate(datetime.value, { zone: timezone.value.name });
		params.datetime = luxonDate.toISO()!;
	}
	try {
		await service.updateTime(params);
		await getTime();
		toast.success(
			i18n.t('components.config.time.messages.save.success'),
		);
	} catch {
		toast.error('TODO ERROR HANDLING');
	}
}

async function getTimezones(): Promise<void> {
	timezones.value = await service.listTimezones();
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
	} else if (timeSet.value.ntpServers !== undefined) {
		timeSet.value.ntpServers[index] = server;
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
