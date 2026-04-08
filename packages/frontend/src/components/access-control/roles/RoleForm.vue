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
			<IActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
				container-type='card-title'
				:tooltip='$t(`components.accessControl.roles.actions.${action}`)'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Action'
			@submit.prevent='onSubmit()'
		>
			<ICard :action='action'>
				<template #title>
					{{ $t(`components.accessControl.roles.actions.${action}`) }}
				</template>
				<ITextInput
					v-model='roleConfig.name'
					:label='$t("components.accessControl.roles.roleName.name")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.accessControl.roles.roleName.required")),
					]'
					required
				/>
				<ITextInput
					v-model='roleConfig.description'
					:label='$t("common.labels.description")'
				/>
				<ScopeTable
					:selected='roleConfig.scopes'
					@update='updateScopes'
				/>
				<template #actions>
					<IActionBtn
						:action='action'
						container-type='card'
						:loading='componentState === ComponentState.Action'
						:disabled='!isValid.value || componentState === ComponentState.Action'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
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
import { AccessScope, RoleConfig, RoleInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { Action, ComponentState, IActionBtn, ICard, IModalWindow, ITextInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { ref, Ref, type TemplateRef, useTemplateRef, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

import ScopeTable from './ScopeTable.vue';

const componentProps = withDefaults(defineProps<{
	action: Action;
	role?: RoleInfo;
}>(), {
	role: undefined,
});

const emit = defineEmits<{
	update: [role: RoleInfo];
}>();

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const showDialog: Ref<boolean> = ref(false);
const form: TemplateRef<VForm> = useTemplateRef('form');
const roleConfig: Ref<RoleConfig> = ref(getRoleConfig(componentProps.role));
const service = useApiClient().getSecurityServices().getRoleService();
const i18n = useI18n();

/**
 * Creates role configuration object from role info object,
 * or returns default configuration when no role is specified.
 * @param {RoleInfo} role Role to extract config from
 * @return {RoleConfig} configuration for specified role
 */
function getRoleConfig(role?: RoleInfo): RoleConfig {
	return {
		name: role?.name ?? '',
		description: role?.description ?? '',
		scopes: role?.scopes ?? [],
	};
}

/**
 * Calls endpoint that adds new role
 * @param {RoleConfig} config Role configuration
 * @return {Promise<RoleInfo|null>} Full information about new role received from the backend or null when error occurs
 */
async function addRole(config: RoleConfig): Promise<RoleInfo|null> {
	try {
		const result = await service.create(config);
		toast.success(i18n.t('components.accessControl.role.add.success'));
		return result;
	} catch {
		toast.error(i18n.t('components.accessControl.role.add.failure'));
		return null;
	}
}

async function updateRole(id: number, config: RoleConfig): Promise<RoleInfo|null> {
	try {
		const result = await service.update(id, config);
		toast.success(i18n.t('components.accessControl.role.update.success'));
		return result;
	} catch {
		toast.error(i18n.t('components.accessControl.role.update.failure'));
		return null;
	}
}

/**
 * Sends data to backend
 */
async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	let result = null;
	if (componentProps.role?.id) {
		result = await updateRole(componentProps.role.id, roleConfig.value);
	} else {
		result = await addRole(roleConfig.value);
	}
	if (result === null) {
		componentState.value = ComponentState.Error;
		return;
	}
	emit('update', result);
	close();
	componentState.value = ComponentState.Ready;
}

/**
 * Adds / removes scope from scopes array
 * @param {AccessScope} scope Scope to add / remove from array
 */
function updateScopes(scope: AccessScope): void {
	if (roleConfig.value.scopes.includes(scope)) {
		roleConfig.value.scopes = roleConfig.value.scopes.filter((item: AccessScope): boolean => item !== scope);
	} else {
		roleConfig.value.scopes = [...roleConfig.value.scopes, scope];
	}
}

/**
 * Closes the dialog
 */
function close(): void {
	showDialog.value = false;
	roleConfig.value = getRoleConfig(componentProps.role);
}

/**
 * Reset the form on open / close
 */
watch(showDialog, (newValue: boolean): void => {
	if (!newValue) {
		return;
	}
	roleConfig.value = getRoleConfig(componentProps.role);
});
</script>
