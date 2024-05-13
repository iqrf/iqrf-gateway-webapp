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
	<v-form :disabled='componentState === ComponentState.Loading'>
		<Card>
			<template #title>
				{{ $t('components.maintenance.backup.backup.title') }}
			</template>
			<v-table density='compact'>
				<thead>
					<tr>
						<th>{{ $t('components.maintenance.backup.backup.item') }}</th>
						<th>
							<v-btn-group
								class='float-right'
								density='compact'
								variant='elevated'
							>
								<v-btn
									color='primary'
									@click='setAll(true)'
								>
									{{ $t('common.buttons.selectAll') }}
								</v-btn>
								<v-btn
									color='grey-darken-2'
									@click='setAll(false)'
								>
									{{ $t('common.buttons.deselectAll') }}
								</v-btn>
							</v-btn-group>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>{{ $t('components.maintenance.backup.backup.software.iqrf') }}</td>
						<td>
							<v-checkbox-btn
								v-model='params.software.iqrf'
								class='float-right'
							/>
						</td>
					</tr>
					<tr v-if='featureStore.isEnabled(Feature.mender)'>
						<td>{{ $t('components.maintenance.backup.backup.software.mender') }}</td>
						<td>
							<v-checkbox-btn
								v-model='params.software.mender'
								class='float-right'
							/>
						</td>
					</tr>
					<tr v-if='featureStore.isEnabled(Feature.monit)'>
						<td>{{ $t('components.maintenance.backup.backup.software.monit') }}</td>
						<td>
							<v-checkbox-btn
								v-model='params.software.monit'
								class='float-right'
							/>
						</td>
					</tr>
					<tr>
						<td>{{ $t('components.maintenance.backup.backup.system.hostname') }}</td>
						<td>
							<v-checkbox-btn
								v-model='params.system.hostname'
								class='float-right'
							/>
						</td>
					</tr>
					<tr v-if='featureStore.isEnabled(Feature.journal)'>
						<td>{{ $t('components.maintenance.backup.backup.system.journal') }}</td>
						<td>
							<v-checkbox-btn
								v-model='params.system.journal'
								class='float-right'
							/>
						</td>
					</tr>
					<tr v-if='featureStore.isEnabled(Feature.networkManager)'>
						<td>{{ $t('components.maintenance.backup.backup.system.network') }}</td>
						<td>
							<v-checkbox-btn
								v-model='params.system.network'
								class='float-right'
							/>
						</td>
					</tr>
					<tr>
						<td>{{ $t('components.maintenance.backup.backup.system.time') }}</td>
						<td>
							<v-checkbox-btn
								v-model='params.system.time'
								class='float-right'
							/>
						</td>
					</tr>
				</tbody>
			</v-table>
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='componentState === ComponentState.Loading'
					@click='onSubmit'
				>
					{{ $t('common.buttons.backup') }}
				</v-btn>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type BackupService } from '@iqrf/iqrf-gateway-webapp-client/services/Maintenance';
import { Feature } from '@iqrf/iqrf-gateway-webapp-client/types';
import { type GatewayBackup } from '@iqrf/iqrf-gateway-webapp-client/types/Maintenance/Backup';
import { FileDownloader } from  '@iqrf/iqrf-gateway-webapp-client/utils/FileDownloader';
import {
	type Ref,
	ref,
} from 'vue';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import { useApiClient } from '@/services/ApiClient';
import { useFeatureStore } from '@/store/features';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const featureStore = useFeatureStore();
const service: BackupService = useApiClient().getMaintenanceServices().getBackupService();
const params: Ref<GatewayBackup> = ref({
	system: {
		hostname: false,
		journal: false,
		network: false,
		time: false,
	},
	software: {
		iqrf: false,
		mender: false,
		monit: false,
	},
});

function setAll(selected: boolean): void {
	params.value.software = {
		iqrf: selected,
		mender: featureStore.isEnabled(Feature.mender) ? selected : false,
		monit: featureStore.isEnabled(Feature.monit) ? selected : false,
	};
	params.value.system = {
		hostname: selected,
		journal: featureStore.isEnabled(Feature.journal) ? selected : false,
		network: featureStore.isEnabled(Feature.networkManager) ? selected : false,
		time: selected,
	};
}

function onSubmit(): void {
	componentState.value = ComponentState.Loading;
	service.backup(params.value)
		.then((response: ArrayBuffer) => {
			const filename = `iqrf-gateway-backup_${new Date().toISOString()}.zip`;
			FileDownloader.downloadFromData(response, 'application/zip', filename);
			componentState.value = ComponentState.Ready;
		})
		.catch(() => toast.error('TODO ERROR HANDLING'));
}
</script>
