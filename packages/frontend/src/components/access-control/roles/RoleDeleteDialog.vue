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
	<IDeleteModalWindow
		ref='dialog'
		:tooltip='$t("components.accessControl.roles.actions.delete.tooltip")'
		:component-state='componentState'
		:disabled='role.system'
		persistent @submit='onSubmit()'
	>
		<template #title>
			{{ $t('components.accessControl.roles.actions.delete.title') }}
		</template>
		{{ $t('components.accessControl.roles.actions.delete.prompt', { role: role.name }) }}
	</IDeleteModalWindow>
</template>

<script lang='ts' setup>
import { RoleInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { ComponentState, IDeleteModalWindow } from '@iqrf/iqrf-vue-ui';
import { ref, type Ref, type TemplateRef, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';

const componentProps = defineProps<{
	role: RoleInfo;
}>();
const emit = defineEmits<{
	delete: [id: number];
}>();

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const dialog: TemplateRef<InstanceType<typeof IDeleteModalWindow>> = useTemplateRef('dialog');
const service = useApiClient().getSecurityServices().getRoleService();
const i18n = useI18n();

/**
 * Confirm user deletion
 */
async function onSubmit(): Promise<void> {
	componentState.value = ComponentState.Action;
	try {
		await service.delete(componentProps.role.id!);
		emit('delete', componentProps.role.id!);
		toast.success(i18n.t('components.accessControl.roles.actions.delete.success'));
		close();
	} catch {
		toast.error(i18n.t('components.accessControl.roles.actions.delete.failure'));
	}
}

/**
 * Closes the dialog window
 */
function close(): void {
	dialog.value?.close();
}
</script>
