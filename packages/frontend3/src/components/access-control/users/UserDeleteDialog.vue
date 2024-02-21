<template>
	<DeleteModalWindow
		ref='dialog'
		:tooltip='$t("components.accessControl.users.actions.delete")'
		@submit='onSubmit'
	>
		<template #title>
			{{ $t('components.accessControl.users.delete.title') }}
		</template>
		{{ $t('components.accessControl.users.delete.prompt', {user: user.username}) }}
	</DeleteModalWindow>
</template>

<script lang='ts' setup>
import { type UserInfo } from '@iqrf/iqrf-gateway-webapp-client/types';
import { type AxiosError } from 'axios';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';

import DeleteModalWindow from '@/components/DeleteModalWindow.vue';
import { basicErrorToast } from '@/helpers/errorToast';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';

interface Props {
	user: UserInfo
	onlyUser: boolean
}

const userStore = useUserStore();
const i18n = useI18n();
const router = useRouter();
const emit = defineEmits(['refresh']);
const dialog: Ref<typeof DeleteModalWindow | null> = ref(null);
const componentProps = defineProps<Props>();

function onSubmit(): void {
	useApiClient().getUserService().delete(componentProps.user.id)
		.then(async () => {
			toast.success(
				i18n.t('components.accessControl.users.messages.delete.success', { user: componentProps.user.username }),
			);
			if (componentProps.user.id === userStore.getId) {
				close();
				await userStore.signOut();
				if (componentProps.onlyUser) {
					await router.push('/install/');
				}
			} else {
				close();
				emit('refresh');
			}
		})
		.catch((error: AxiosError) => {
			basicErrorToast(error, 'components.accessControl.users.messages.delete.failure', { user: componentProps.user.username });
		});
}

function close(): void {
	dialog.value?.close();
}

</script>
