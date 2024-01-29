<template>
	<DeleteModalWindow
		ref='dialog'
		:tooltip='$t("components.accessControl.sshKeys.actions.delete")'
		@submit='onSubmit'
	>
		<template #title>
			{{ $t('components.accessControl.sshKeys.delete.title') }}
		</template>
		{{ $t('components.accessControl.sshKeys.delete.prompt', {id: sshKey.id}) }}
	</DeleteModalWindow>
</template>

<script lang='ts' setup>
import { type SshKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { type SshKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { ref, type Ref , type PropType } from 'vue';
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
const service: SshKeyService = useApiClient().getGatewayServices().getSshKeyService();

async function onSubmit(): Promise<void> {
	service.deleteKey(componentProps.sshKey.id)
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
