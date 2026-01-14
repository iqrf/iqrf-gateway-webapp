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
	<IDeleteModalWindow
		ref='dialog'
		:tooltip='$t("components.accessControl.sshKeys.actions.delete")'
		:component-state='componentState'
		persistent
		:disabled='disabled'
		@submit='onSubmit()'
	>
		<template #title>
			{{ $t('components.accessControl.sshKeys.delete.title') }}
		</template>
		{{ $t('components.accessControl.sshKeys.delete.prompt', { id: sshKey.id }) }}
	</IDeleteModalWindow>
</template>

<script lang='ts' setup>
import { type SshKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import { type SshKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { ComponentState, IDeleteModalWindow } from '@iqrf/iqrf-vue-ui';
import { ref, type Ref, type TemplateRef, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';

const componentProps = withDefaults(
	defineProps<{
		sshKey: SshKeyInfo;
		disabled?: boolean;
	}>(),
	{
		disabled: false,
	},
);
const emit = defineEmits<{
	refresh: [];
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const i18n = useI18n();
const dialog: TemplateRef<InstanceType<typeof IDeleteModalWindow>> = useTemplateRef('dialog');
const service: SshKeyService = useApiClient()
	.getSecurityServices()
	.getSshKeyService();

async function onSubmit(): Promise<void> {
	componentState.value = ComponentState.Action;
	const translationParams = { id: componentProps.sshKey.id };
	try {
		await service.deleteKey(componentProps.sshKey.id);
		toast.success(
			i18n.t('components.accessControl.sshKeys.messages.delete.success', translationParams),
		);
		close();
		emit('refresh');
	} catch {
		componentState.value = ComponentState.Ready;
		toast.error(
			i18n.t('components.accessControl.sshKeys.messages.delete.failed', translationParams),
		);
	}
}

function close(): void {
	dialog.value?.close();
}
</script>
