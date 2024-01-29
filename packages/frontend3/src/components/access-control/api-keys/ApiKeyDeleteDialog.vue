<template>
	<DeleteModalWindow
		ref='dialog'
		:tooltip='$t("components.accessControl.apiKeys.actions.delete")'
		@submit='onSubmit'
	>
		<template #title>
			{{ $t('components.accessControl.apiKeys.delete.title') }}
		</template>
		{{ $t('components.accessControl.apiKeys.delete.prompt', {id: apiKey.id}) }}
	</DeleteModalWindow>
</template>

<script lang='ts' setup>
import { type ApiKeyService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { type ApiKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types';
import { ref, type Ref , type PropType } from 'vue';
import { toast } from 'vue3-toastify';

import DeleteModalWindow from '@/components/DeleteModalWindow.vue';
import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits(['refresh']);
const dialog: Ref<typeof DeleteModalWindow | null> = ref(null);
const componentProps = defineProps({
	apiKey: {
		type: Object as PropType<ApiKeyInfo>,
		required: true,
	},
});
const service: ApiKeyService = useApiClient().getApiKeyService();

async function onSubmit(): Promise<void> {
	service.delete(componentProps.apiKey.id!)
		.then(() => {
			close();
			emit('refresh');
		})
		.catch(() => toast.error('TODO ERROR HANDLING'));
}

function close(): void {
	dialog.value?.close();
}
</script>
