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
		v-model='show'
		persistent
	>
		<template #activator='{ props }'>
			<IActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
				container-type='card-title'
				:tooltip='$t("components.accessControl.apiKeys.actions.add")'
				:disabled='disabled'
			/>
			<IDataTableAction
				v-else
				v-bind='props'
				:action='action'
				:tooltip='$t("components.accessControl.apiKeys.actions.edit")'
				:disabled='disabled'
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
					{{ dialogTitle }}
				</template>
				<v-alert
					v-if='key.legacy'
					color='warning'
					variant='tonal'
					:text='$t("components.accessControl.apiKeys.legacyKeyWarning")'
					class='mb-5'
				/>
				<v-skeleton-loader
					class='input-skeleton-loader'
					:loading='componentState === ComponentState.Loading'
					type='heading@8, button'
				>
					<ITextInput
						v-model='key.description'
						:prepend-inner-icon='mdiTextShort'
						:label='$t("common.labels.description")'
						:rules='[
							(v: string|null) => ValidationRules.required(v, $t("components.accessControl.apiKeys.validations.description.required")),
						]'
						required
					/>
					<IDateTimeInput
						v-model='expiration'
						:label='$t("components.accessControl.apiKeys.expiration")'
						:min='toRaw(minDate)'
					/>
					<RoleSelect
						v-model='role'
						:role-list='componentProps.roleList'
					/>
					<ITextInput
						v-if='key.revokedBy'
						v-model='key.revokedBy'
						:label='$t("components.accessControl.apiKeys.revokedBy")'
						disabled
					/>
					<IDateTimeInput
						v-if='key.revokedAt'
						v-model='key.revokedAt'
						:label='$t("components.accessControl.apiKeys.revokedAt")'
						disabled
					/>
				</v-skeleton-loader>
				<template #actions>
					<IActionBtn
						:action='action'
						:loading='componentState === ComponentState.Action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<ApiKeyRevokeDialog
						v-if='action === Action.Edit'
						:api-key='key'
						appearance='button'
						@revoke='emit("refresh")'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						:disabled='componentState === ComponentState.Action'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
	<ApiKeyDisplayDialog
		ref='displayDialog'
		:api-key='generatedKey'
		@closed='clear'
	/>
</template>

<script lang='ts' setup>
import { type ApiKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import {
	ApiKeyConfig,
	type ApiKeyCreated,
	type ApiKeyInfo,
	RoleInfo,
} from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { DateTimeUtils } from '@iqrf/iqrf-gateway-webapp-client/utils';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTableAction,
	IDateTimeInput,
	IModalWindow,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiTextShort } from '@mdi/js';
import { DateTime } from 'luxon';
import { computed, ref, type Ref, type TemplateRef, toRaw, useTemplateRef, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import ApiKeyDisplayDialog from '@/components/access-control/api-keys/ApiKeyDisplayDialog.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

import RoleSelect from '../roles/RoleSelect.vue';

import ApiKeyRevokeDialog from './ApiKeyRevokeDialog.vue';


const componentProps = withDefaults(
	defineProps<{
		action?: Action.Add | Action.Edit;
		apiKey?: ApiKeyInfo;
		disabled?: boolean;
		roleList: RoleInfo[];
	}>(),
	{
		action: Action.Add,
		apiKey: undefined,
	},
);
const emit = defineEmits<{
	refresh: [];
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const service: ApiKeyService = useApiClient()
	.getSecurityServices()
	.getApiKeyService();
const form: TemplateRef<VForm> = useTemplateRef('form');
const defaultKey: ApiKeyInfo = {
	description: '',
	expiration: null,
	roleId: -1,
	legacy: false,
};
const expiration: Ref<DateTime | null> = ref(null);
const key: Ref<ApiKeyInfo> = ref(defaultKey);
const generatedKey: Ref<string | null> = ref(null);
const displayDialog: Ref<InstanceType<typeof ApiKeyDisplayDialog>|null> = useTemplateRef('displayDialog');
const minDate: Ref<DateTime | null> = ref(null);
const role: Ref<RoleInfo | undefined> = ref(undefined);

const dialogTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.accessControl.apiKeys.actions.add');
	}
	return i18n.t('components.accessControl.apiKeys.actions.edit');
});

/**
 * Updates the roleId in API key when user selects new role.
 */
watch(role, () => {
	if (!role.value) {
		return;
	}
	key.value.roleId = role.value.id!;
});

/**
 * Set form default values
 */
watch(show, (newVal: boolean): void => {
	if (!newVal) {
		return;
	}
	if (!componentProps.roleList || componentProps.roleList.length === 0) {
		componentState.value = ComponentState.Error;
		toast.error(i18n.t('components.accessControl.roles.messages.list.failed'));
		return;
	}
	if (componentProps.action === Action.Add) {
		key.value = { ...defaultKey };
	} else if (componentProps.action === Action.Edit) {
		if (componentProps.apiKey) {
			key.value = { ...componentProps.apiKey };
		} else {
			key.value = { ...defaultKey };
		}
	}
	if (key.value.expiration !== null) {
		expiration.value = key.value.expiration;
	} else {
		expiration.value = null;
	}
	minDate.value = DateTime.now();
	let defaultRole: RoleInfo | undefined = undefined;
	if (key.value.roleId === -1) {
		defaultRole = componentProps.roleList.find((role) => role.systemKey === 'normal');
	} else {
		defaultRole = componentProps.roleList.find((role) => role.id === key.value.roleId);
	}
	if (!defaultRole) {
		componentState.value = ComponentState.Error;
		toast.error(i18n.t('components.accessControl.roles.messages.defaultRoleUnavailable'));
	} else {
		selectRole(defaultRole);
	}
});

/**
 * Selects new key role
 * @param {RoleInfo} newRole New key role
 */
function selectRole(newRole: RoleInfo | undefined): void {
	key.value.roleId = newRole?.id!;
	role.value = newRole;
}

/**
 * Validates the form and creates / updates the API key.
 */
async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	if (role.value === undefined) {
		toast.error(i18n.t('components.accessControl.roles.messages.selectionRequired'));
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...key.value };
	console.warn(params, expiration.value);
	if (expiration.value !== null) {
		params.expiration = DateTimeUtils.deserialize(expiration.value.toJSDate().toISOString());
	}
	const config: ApiKeyConfig = {
		description: params.description,
		expiration: params.expiration,
		roleId: params.roleId,
	};
	try {
		if (componentProps.action === Action.Add) {
			const createdKey: ApiKeyCreated = await service.create(config);
			generatedKey.value = createdKey.key;
			toast.success(
				i18n.t('components.accessControl.apiKeys.messages.save.success'),
			);
			close();
			emit('refresh');
			displayDialog.value?.open();
		} else if (componentProps.action === Action.Edit) {
			await service.update(params.id!, config);
			close();
			emit('refresh');
		}
	} catch {
		toast.error(
			i18n.t('components.accessControl.apiKeys.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

/**
 * Clears the key value.
 */
function clear(): void {
	generatedKey.value = null;
}

/**
 * Closes the dialog window and restores default key.
 */
function close(): void {
	show.value = false;
	key.value = { ...defaultKey };
}
</script>
