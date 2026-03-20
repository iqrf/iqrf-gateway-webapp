<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

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
	<v-expansion-panels>
		<v-expansion-panel>
			<v-expansion-panel-title>
				{{ $t('components.accessControl.scopeTable.title') }}
			</v-expansion-panel-title>
			<v-expansion-panel-text>
				<ICard>
					<IDataTable
						:headers="headers"
						:items="scopes"
						:items-per-page="scopes.length"
						hide-pagination
						fixed-header
						height="400"
					>
						<template #item.actions='{ item }'>
							<v-checkbox-btn
								:model-value="item.selected"
								@update:model-value="emit('update', item.value)"
							/>
						</template>
					</IDataTable>
				</ICard>
			</v-expansion-panel-text>
		</v-expansion-panel>
	</v-expansion-panels>
</template>

<script lang='ts' setup>
import { ICard, IDataTable } from '@iqrf/iqrf-vue-ui';
import { AccessScope } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const componentProps = defineProps<{
	selected: Array<AccessScope>
}>();
const emit = defineEmits<{
	update: [scopeValue: AccessScope]
}>();

const i18n = useI18n();
const headers = [
	{ key: 'value', title: i18n.t('components.accessControl.scopeTable.columns.value') },
	{ key: 'title', title: i18n.t('components.accessControl.scopeTable.columns.title') },
	{ key: 'description', title: i18n.t('components.accessControl.scopeTable.columns.description') },
	{ key: 'actions', title: i18n.t('components.accessControl.scopeTable.columns.selected'), align: 'end', sortable: false}
];
const scopes = computed(
	() => Object.values(AccessScope).map(
		(scopeValue: AccessScope) => generateScopeObject(scopeValue, componentProps.selected)
	)
);

function generateScopeObject(scope: AccessScope, selectedScopes: Array<AccessScope>): object {
	return {
		value: scope,
		title: i18n.t(`components.accessControl.scopeTable.scopes.${scope}.title`),
		description: i18n.t(`components.accessControl.scopeTable.scopes.${scope}.description`),
		selected: selectedScopes.includes(scope),
	};
}

</script>
