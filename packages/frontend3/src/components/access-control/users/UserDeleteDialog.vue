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
import { PropType, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';

import DeleteModalWindow from '@/components/DeleteModalWindow.vue';
import { useApiClient } from '@/services/ApiClient';
import { useInstallStore } from '@/store/install';
import { useUserStore } from '@/store/user';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const componentProps = defineProps({
	user: {
		type: Object as PropType<UserInfo>,
		required: true,
	},
	onlyUser: {
		type: Boolean,
		required: true,
	},
});
const emit = defineEmits(['refresh']);
const installStore = useInstallStore();
const userStore = useUserStore();
const i18n = useI18n();
const router = useRouter();
const dialog: Ref<typeof DeleteModalWindow | null> = ref(null);

/**
 * Confirm user deletion
 */
async function onSubmit(): Promise<void> {
	componentState.value = ComponentState.Saving;
	try {
		await useApiClient().getSecurityServices().getUserService().delete(componentProps.user.id);
		componentState.value = ComponentState.Ready;
		toast.success(
			i18n.t('components.accessControl.users.messages.delete.success', { user: componentProps.user.username }),
		);
		if (componentProps.user.id === userStore.getId) {
			close();
			await userStore.signOut();
			if (componentProps.onlyUser) {
				installStore.setHasUsers(false);
				await router.push({ name: 'InstallationWizard' });
			}
		} else {
			close();
			emit('refresh');
		}
	} catch {
		componentState.value = ComponentState.Ready;
		toast.error(
			i18n.t('components.accessControl.users.messages.delete.failure', { user: componentProps.user.username }),
		);
	}
}

/**
 * Closes the dialog window
 */
function close(): void {
	dialog.value?.close();
}

</script>
