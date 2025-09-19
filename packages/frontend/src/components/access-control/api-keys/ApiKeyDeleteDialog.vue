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
		:tooltip='$t("components.accessControl.apiKeys.actions.delete")'
		@submit='onSubmit'
	>
		<template #title>
			{{ $t('components.accessControl.apiKeys.delete.title') }}
		</template>
		{{ $t('components.accessControl.apiKeys.delete.prompt', { id: apiKey.id }) }}
	</IDeleteModalWindow>
</template>

<script lang='ts' setup>
import { type ApiKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import { type ApiKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { IDeleteModalWindow } from '@iqrf/iqrf-vue-ui';
import { type PropType, ref , type Ref } from 'vue';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits(['refresh']);
const dialog: Ref<typeof IDeleteModalWindow | null> = ref(null);
const componentProps = defineProps({
	apiKey: {
		type: Object as PropType<ApiKeyInfo>,
		required: true,
	},
});
const service: ApiKeyService = useApiClient().getSecurityServices().getApiKeyService();

async function onSubmit(): Promise<void> {
	try {
		await service.delete(componentProps.apiKey.id!);
		close();
		emit('refresh');
	} catch {
		toast.error('TODO ERROR HANDLING');
	}
}

function close(): void {
	dialog.value?.close();
}
</script>
