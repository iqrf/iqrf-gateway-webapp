<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		:tooltip='$t("components.accessControl.users.actions.delete")'
		@submit='onSubmit'
	>
		<template #title>
			{{ $t('components.accessControl.users.delete.title') }}
		</template>
		{{ $t('components.accessControl.users.delete.prompt', { user: user.username }) }}
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
	useApiClient().getSecurityServices().getUserService().delete(componentProps.user.id)
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
