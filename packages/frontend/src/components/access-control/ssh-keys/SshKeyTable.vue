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
			{{ $t('pages.accessControl.sshKeys.title') }}
		</template>
		<template #titleActions>
			<SshKeyForm
				:action='Action.Add'
				:key-types='types'
				@refresh='getKeys()'
			/>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				:tooltip='$t("components.accessControl.sshKeys.actions.refresh")'
				@click='getKeys()'
			/>
		</template>
		<IDataTable
			:headers='headers'
			:items='keys'
			:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
			:no-data-text='noDataText'
			:hover='true'
			:dense='true'
		>
			<template #item.createdAt='{ item }'>
				{{ $d(item.createdAt.toJSDate(), 'long') }}
			</template>
			<template #item.actions='{ item, internalItem, toggleExpand, isExpanded }'>
				<IDataTableAction
					color='primary'
					:icon='mdiInformation'
					:tooltip='isExpanded(internalItem) ? $t("components.accessControl.sshKeys.actions.hideInfo") : $t("components.accessControl.sshKeys.actions.showInfo")'
					@click='toggleExpand(internalItem)'
				/>
				<SshKeyDeleteDialog
					:ssh-key='toRaw(item)'
					@refresh='getKeys()'
				/>
			</template>
			<template #expanded-row='{ columns, item }'>
				<td :colspan='columns.length'>
					<v-sheet border>
						<v-table density='compact'>
							<tbody>
								<tr>
									<th>{{ $t('components.accessControl.sshKeys.table.hash') }}</th>
									<td>
										{{ item.hash }}
										<v-icon
											color='primary'
											:icon='mdiContentCopy'
											@click='copyToClipboard(item.hash)'
										/>
									</td>
								</tr>
								<tr>
									<th>{{ $t('components.accessControl.sshKeys.table.key') }}</th>
									<td>
										{{ item.key }}
										<v-icon
											color='primary'
											:icon='mdiContentCopy'
											@click='copyToClipboard(item.key)'
										/>
									</td>
								</tr>
							</tbody>
						</v-table>
					</v-sheet>
				</td>
			</template>
		</IDataTable>
	</ICard>
</template>

<script lang='ts' setup>
import { SshKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import { type SshKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { Action, IActionBtn, ICard, IDataTable, IDataTableAction } from '@iqrf/iqrf-vue-ui';
import { mdiContentCopy, mdiInformation } from '@mdi/js';
import { computed, onMounted, ref, type Ref, toRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import SshKeyDeleteDialog from '@/components/access-control/ssh-keys/SshKeyDeleteDialog.vue';
import SshKeyForm from '@/components/access-control/ssh-keys/SshKeyForm.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: SshKeyService = useApiClient().getSecurityServices().getSshKeyService();
const headers = [
	{ key: 'id', title: i18n.t('common.columns.id') },
	{ key: 'description', title: i18n.t('common.columns.description') },
	{ key: 'type', title: i18n.t('components.accessControl.sshKeys.table.type') },
	{ key: 'createdAt', title: i18n.t('components.accessControl.sshKeys.table.createdAt') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end', sortable: false },
];
const types: Ref<string[]> = ref([]);
const keys: Ref<SshKeyInfo[]> = ref([]);

const noDataText = computed(() => {
	if (componentState.value === ComponentState.FetchFailed) {
		return i18n.t('components.accessControl.sshKeys.noData.fetchError');
	}
	return i18n.t('components.accessControl.sshKeys.noData.empty');
});

onMounted(async (): Promise<void> => {
	await getKeys();
	await getKeyTypes();
});

async function getKeys(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		keys.value = await service.list();
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.accessControl.sshKeys.messages.list.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

async function getKeyTypes(): Promise<void> {
	try {
		types.value = await service.listKeyTypes();
	} catch {
		toast.error(
			i18n.t('components.accessControl.sshKeys.messages.listTypes.failed'),
		);
	}
}

function copyToClipboard(content: string): void {
	navigator.clipboard.writeText(content);
}

</script>
