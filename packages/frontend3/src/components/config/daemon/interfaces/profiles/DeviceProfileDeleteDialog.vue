<template>
	<DeleteModalWindow
		ref='dialog'
		:tooltip='$t("components.configuration.profiles.actions.delete")'
		@submit='onSubmit'
	>
		<template #title>
			{{ $t('components.configuration.profiles.delete.title') }}
		</template>
		{{ $t('components.configuration.profiles.delete.prompt', {name: profile.name}) }}
	</DeleteModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayDaemonMapping } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { type Ref, ref , type PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import DeleteModalWindow from '@/components/DeleteModalWindow.vue';
import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits(['deleted']);
const componentProps = defineProps({
	profile: {
		type: Object as PropType<IqrfGatewayDaemonMapping>,
		required: true,
	},
});
const i18n = useI18n();
const dialog: Ref<typeof DeleteModalWindow | null> = ref(null);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();

async function onSubmit(): Promise<void> {
	if (componentProps.profile.id === undefined) {
		return;
	}
	service.deleteMapping(componentProps.profile.id)
		.then(() => {
			toast.success(
				i18n.t('components.configuration.profiles.messages.delete.success', { name: componentProps.profile.name }),
			);
			close();
			emit('deleted');
		})
		.catch(() => toast.error('TODO ERROR HANDLING'));
}

function close(): void {
	dialog.value?.close();
}
</script>
