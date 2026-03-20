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
		v-if='action === Action.Disable'
		v-model='showDialog'
		persistent
	>
		<template #activator='scope'>
			<IActionBtn
				v-if='componentProps.appearance === "button"'
				v-bind="scope.props"
				color="red"
				container-type="card"
				:icon="mdiCloseBox"
				:text='$t("components.accessControl.apiKeyRevocationDialog.tooltip.revoke")'
				:disabled='componentProps.apiKey.legacy'
			/>
			<IDataTableAction
				v-else-if='componentProps.appearance === "tableAction"'
				v-bind='scope.props'
				color='red'
				:icon='mdiCloseBox'
				:tooltip='$t("components.accessControl.apiKeyRevocationDialog.tooltip.revoke")'
			/>
		</template>
		<ICard header-color='red'>
			<template #title>
				{{ $t('components.accessControl.apiKeyRevocationDialog.title') }}
			</template>
			{{ $t('components.accessControl.apiKeyRevocationDialog.prompt') }}
			<template #actions>
				<IActionBtn
					color='red'
					container-type='card'
					:icon='mdiCloseBox'
					:loading='componentState === ComponentState.Action'
					:text='$t("components.accessControl.apiKeyRevocationDialog.revokeButtonText")'
					@click='revoke()'
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
	</IModalWindow>
	<IDataTableAction
		v-else-if='action === Action.Enable && componentProps.appearance === "tableAction"'
		color='red'
		:icon='mdiCloseBox'
		:loading='componentState === ComponentState.Action'
		:tooltip='$t("components.accessControl.apiKeyRevocationDialog.tooltip.revoked")'
		disabled
	/>
	<IDataTableAction
		v-else-if='action === Action.Cancel && componentProps.appearance === "tableAction"'
		color='gray'
		:icon='mdiCloseBoxOutline'
		:loading='componentState === ComponentState.Action'
		:tooltip='$t("components.accessControl.apiKeyRevocationDialog.tooltip.legacy")'
		disabled
	/>
	<IActionBtn
		v-else-if='action === Action.Enable && componentProps.appearance === "button"'
		color='red'
		:icon='mdiCloseBox'
		:loading='componentState === ComponentState.Action'
		:text='$t("components.accessControl.apiKeyRevocationDialog.tooltip.revoked")'
		disabled
	/>
	<IActionBtn
		v-else
		color='gray'
		:icon='mdiCloseBoxOutline'
		:loading='componentState === ComponentState.Action'
		:text='$t("components.accessControl.apiKeyRevocationDialog.tooltip.legacy")'
		disabled
	/>
</template>

<script setup lang='ts'>
import { useApiClient } from '@/services/ApiClient';
import { ApiKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import { ApiKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTableAction,
	IModalWindow,
} from '@iqrf/iqrf-vue-ui';
import { mdiCloseBox, mdiCloseBoxOutline } from '@mdi/js';
import { computed, type ComputedRef, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

/// Component props
const componentProps = withDefaults(
	defineProps<{
		/// Key to revoke
		apiKey: ApiKeyInfo,
		/// Choose dialog appearance on the page
		appearance?: 'tableAction' | 'button';
	}>(), {
		appearance: 'tableAction',
	},
);
/// Component emits
const emit = defineEmits<{
	revoke: [];
}>();
/// i18n translation API
const i18n = useI18n();
/// API client for working with the webapp backend
const service: ApiKeyService = useApiClient().getSecurityServices().getApiKeyService();
/// Action
const action: ComputedRef<Action> = computed((): Action => {
	if (componentProps.apiKey.legacy) {
		return Action.Cancel;
	}
	if (componentProps.apiKey.state === 'revoked') {
		return Action.Enable;
	}
	return Action.Disable;
});
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Show dialog window
const showDialog: Ref<boolean> = ref(false);

/**
 * Revocation function. Revokes the API key.
 */
async function revoke(): Promise<void> {
	if (componentProps.apiKey.legacy) {
		toast.error(
			i18n.t('components.accessControl.apiKeys.messages.revoke.legacy'),
		);
		return;
	}
	componentState.value = ComponentState.Action;
	try {
		await service.revoke(componentProps.apiKey.id!);
		toast.success(
			i18n.t('components.accessControl.apiKeys.messages.revoke.success'),
		);
		emit('revoke');
		close();
	} catch {
		toast.error(
			i18n.t('components.accessControl.apiKeys.messages.revoke.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

/**
 * Close the dialog window
 */
function close(): void {
	showDialog.value = false;
}

defineExpose({
	close,
});
</script>
