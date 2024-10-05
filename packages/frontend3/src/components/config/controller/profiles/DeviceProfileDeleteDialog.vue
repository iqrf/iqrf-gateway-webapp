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
		:tooltip='$t("components.configuration.profiles.actions.delete")'
		@submit='onSubmit'
	>
		<template #title>
			{{ $t('components.configuration.profiles.delete.title') }}
		</template>
		{{ $t('components.configuration.profiles.delete.prompt', { name: profile.name }) }}
	</DeleteModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayControllerService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type IqrfGatewayControllerMapping } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { type Ref, ref , type PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import DeleteModalWindow from '@/components/DeleteModalWindow.vue';
import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits(['deleted']);
const dialog: Ref<typeof DeleteModalWindow | null> = ref(null);
const componentProps = defineProps({
	profile: {
		type: Object as PropType<IqrfGatewayControllerMapping>,
		required: true,
	},
});
const i18n = useI18n();
const service: IqrfGatewayControllerService = useApiClient().getConfigServices().getIqrfGatewayControllerService();

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
