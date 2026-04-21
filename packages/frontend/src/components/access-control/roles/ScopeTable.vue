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
	<IModalWindow
		v-model='showDialog'
		persistent
	>
		<template #activator='{ props }'>
			<v-btn
				v-bind='props'
				block
				variant='outlined'
				height='56'
				class='scope-table-activator text-none rounded-t-lg rounded-b-0'
			>
				<div class='scope-table-activator__content'>
					<span class='scope-table-activator__label'>
						{{ $t('components.accessControl.scopeTable.label') }}
					</span>
					<span class='scope-table-activator__value'>
						{{ selectedScopesText }}
					</span>
				</div>
				<v-icon :icon='mdiChevronDown' />
			</v-btn>
		</template>
		<v-form>
			<ICard>
				<template #title>
					{{ $t('components.accessControl.scopeTable.title') }}
				</template>
				<IDataTable
					:headers='headers'
					:items='tableItems'
					:items-per-page='scopes.length'
					hide-pagination
					fixed-header
					height='400'
				>
					<template #item.actions='{ item }'>
						<v-checkbox-btn
							:model-value='item.selected'
							:disabled='disableEdit'
							@update:model-value='update(item.value)'
						/>
					</template>
				</IDataTable>
				<template #actions>
					<v-spacer />
					<IActionBtn
						:action='Action.Close'
						container-type='card'
						:disabled='componentState === ComponentState.Action'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { AccessScope } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { Action, ComponentState, IActionBtn, ICard, IDataTable, IModalWindow } from '@iqrf/iqrf-vue-ui';
import { mdiChevronDown } from '@mdi/js';
import { computed, type ComputedRef, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';

const componentProps = withDefaults(
	defineProps<{
		selected: Array<AccessScope>;
		disableEdit?: boolean;
	}>(),
	{
		disableEdit: false,
	},
);
const emit = defineEmits<{
	update: [scopeValue: AccessScope];
}>();

const showDialog: Ref<boolean> = ref(false);
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const headers = [
	{ key: 'value', title: i18n.t('components.accessControl.scopeTable.columns.value') },
	{ key: 'title', title: i18n.t('components.accessControl.scopeTable.columns.title') },
	{ key: 'description', title: i18n.t('components.accessControl.scopeTable.columns.description') },
	{ key: 'actions', title: i18n.t('components.accessControl.scopeTable.columns.selected'), align: 'end', sortable: false },
];
const scopes = computed(
	() => Object.values(AccessScope).map(
		(scopeValue: AccessScope) => generateScopeObject(scopeValue, componentProps.selected),
	),
);
const tableItems = computed(() => {
	if (!componentProps.disableEdit) {
		return scopes.value;
	}
	return scopes.value.filter((scope: any) => scope.selected);
});
const selectedScopesText: ComputedRef<string> = computed((): string => {
	const count = componentProps.selected.length;
	if (count === 0) {
		return i18n.t('components.accessControl.scopeTable.noneSelected');
	}
	return i18n.t('components.accessControl.scopeTable.selectedCount', { count });
});

/**
 * Closes the dilog window
 */
function close(): void {
	showDialog.value = false;
}

/**
 * Emits the update when scope is selected
 * @param {AccessScope} scope Selected access scope
 */
function update(scope: AccessScope): void {
	if (componentProps.disableEdit) {
		return;
	}
	emit('update', scope);
}

/**
 * Genertes object that is then rendered in the scope array
 * @param {AccessScope} scope Scope to generate scope object for
 * @param {Array<AccessScope>} selectedScopes Array of selected scopes - used to mark scopes selected in the object
 * @return {object} Scope object for rendering in the array
 */
function generateScopeObject(scope: AccessScope, selectedScopes: Array<AccessScope>): object {
	return {
		value: scope,
		title: i18n.t(`components.accessControl.scopeTable.scopes.${scope}.title`),
		description: i18n.t(`components.accessControl.scopeTable.scopes.${scope}.description`),
		selected: selectedScopes.includes(scope),
	};
}

</script>

<style scoped>
.scope-table-activator {
	justify-content: space-between;
	padding-inline: 16px;
}

.scope-table-activator__content {
	display: flex;
	flex: 1 1 auto;
	flex-direction: column;
	align-items: flex-start;
	overflow: hidden;
}

.scope-table-activator__label {
	font-size: 0.75rem;
	line-height: 1;
	color: rgb(var(--v-theme-on-surface), 0.6);
	margin-bottom: 0.25rem;
}

.scope-table-activator__value {
	font-size: 1rem;
	line-height: 1.25rem;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
</style>
