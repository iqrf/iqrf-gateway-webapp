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
	<v-form
		:disabled='[ComponentState.Action, ComponentState.Reloading].includes(componentState)'
		@submit.prevent='setTime()'
	>
		<ICard>
			<template #title>
				{{ $t('pages.config.time.title') }}
			</template>
			<template #titleActions>
				<IActionBtn
					:action='Action.Reload'
					container-type='card-title'
					:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					:disabled='componentState === ComponentState.Action'
					:tooltip='$t("components.config.time.actions.refresh")'
					@click='getTime()'
				/>
			</template>
			<v-alert
				v-if='componentState === ComponentState.FetchFailed'
				type='error'
				variant='tonal'
				:text='$t("components.config.time.messages.fetch.failed")'
			/>
			<v-skeleton-loader
				v-else
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='heading@2, text, table-heading, table-row-divider@2, table-row'
			>
				<v-responsive>
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
						density='compact'
						:hide-details='!timeSet.ntpSync'
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
										:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
										@save='saveServer'
									/>
									<IActionBtn
										class='rounded-e'
										:action='Action.Delete'
										container-type='card-title'
										:tooltip='$t("components.config.time.ntpServers.actions.deleteAll")'
										:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
										@click='clearServers()'
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
								:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
								@save='saveServer'
							/>
							<IDataTableAction
								:action='Action.Delete'
								:disabled='[ComponentState.Action, ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
								:tooltip='$t("components.config.time.ntpServers.actions.delete")'
								@click='removeServer(index)'
							/>
						</template>
					</IDataTable>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<IActionBtn
					:action='Action.Save'
					:loading='componentState === ComponentState.Action'
					:disabled='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
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
import { mdiMapClock } from '@mdi/js';
import { DateTime } from 'luxon';
import { computed, onMounted, ref, type Ref, toRaw } from 'vue';
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
const headers = computed(() => [
	{ key: 'server', title: i18n.t('components.config.time.ntpServers.address') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end' },
]);

async function getTime(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		const data = await service.getTime();
		gatewayTime.value = data;
		timezone.value = {
			name: data.zoneName,
			code: data.abbrevation,
			offset: data.gmtOffset,
		};
		timeSet.value = {
			ntpSync: data.ntpSync,
			ntpServers: data.ntpServers,
			zoneName: data.zoneName,
		};
		datetime.value = datetime.value = DateTime.fromSeconds(gatewayTime.value.utcTimestamp, { zone: gatewayTime.value.zoneName }).toJSDate();
		componentState.value = ComponentState.Ready;

	} catch {
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
		toast.error(
			i18n.t('components.config.time.messages.fetch.failed'),
		);
	}
}

async function setTime(): Promise<void> {
	componentState.value = ComponentState.Action;
	const params: TimeSet = structuredClone(toRaw(timeSet.value));
	params.zoneName = timezone.value.name;
	if (!params.ntpSync) {
		delete params.ntpServers;
	}
	try {
		await service.updateTime(params);
		await getTime();
		toast.success(
			i18n.t('components.config.time.messages.save.success'),
		);
	} catch {
		componentState.value = ComponentState.Ready;
		toast.error(
			i18n.t('components.config.time.messages.save.failed'),
		);
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

onMounted(async () => {
	await getTime();
	await getTimezones();
});
</script>
