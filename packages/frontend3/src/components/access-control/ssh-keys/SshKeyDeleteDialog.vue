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
	<DeleteModalWindow
		ref='dialog'
		:tooltip='$t("components.accessControl.sshKeys.actions.delete")'
		@submit='onSubmit()'
	>
		<template #title>
			{{ $t('components.accessControl.sshKeys.delete.title') }}
		</template>
		{{ $t('components.accessControl.sshKeys.delete.prompt', { id: sshKey.id }) }}
	</DeleteModalWindow>
</template>

<script lang='ts' setup>
import { type SshKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import { type SshKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { type PropType, ref , type Ref } from 'vue';
import { toast } from 'vue3-toastify';

import DeleteModalWindow from '@/components/DeleteModalWindow.vue';
import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits(['refresh']);
const dialog: Ref<typeof DeleteModalWindow | null> = ref(null);
const componentProps = defineProps({
	sshKey: {
		type: Object as PropType<SshKeyInfo>,
		required: true,
	},
});
const service: SshKeyService = useApiClient().getSecurityServices().getSshKeyService();

async function onSubmit(): Promise<void> {
	try {
		await service.deleteKey(componentProps.sshKey.id);
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
