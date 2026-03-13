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
	<IModalWindow
		v-model='show'
		persistent
	>
		<ICard
			:action='Action.Reset'
			header-color='warning'
		>
			<template #title>
				{{ $t('components.accessControl.daemonAccessTokens.rotate.title') }}
			</template>
			{{ $t('components.accessControl.daemonAccessTokens.rotate.prompt', { id: token }) }}
			<template #actions>
				<IActionBtn
					:action='Action.Reset'
					color='warning'
					:text='$t("common.buttons.rotate")'
					container-type='card'
					:disabled='componentState === ComponentState.Action'
					@click='onSubmit()'
				/>
				<v-spacer />
				<IActionBtn
					:action='Action.Cancel'
					container-type='card'
					:disabled='componentState === ComponentState.Action'
					@click='close()'
				/>
			</template>
		</ICard>
	</IModalWindow>
	<DaemonAccessTokenDisplayDialog
		ref='displayDialog'
	/>
</template>

<script lang='ts' setup>
import { Action, ComponentState, IActionBtn, ICard, IModalWindow } from '@iqrf/iqrf-vue-ui';
import { ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import DaemonAccessTokenDisplayDialog from '@/components/access-control/daemon-access-tokens/DaemonAccessTokenDisplayDialog.vue';
import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits<{
	refresh: [];
}>();

const componentState = ref<ComponentState>(ComponentState.Idle);
const i18n = useI18n();
const show = ref<boolean>(false);
let token: number | null = null;
const displayDialog = useTemplateRef<InstanceType<typeof DaemonAccessTokenDisplayDialog>|null>('displayDialog');

async function onSubmit(): Promise<void> {
	if (token === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	try {
		const newToken = await useApiClient()
			.getSecurityServices()
			.getDaemonApiTokenService()
			.rotate(token);
		toast.success(
			i18n.t('components.accessControl.daemonAccessTokens.messages.rotate.success', { id: token }),
		);
		close();
		emit('refresh');
		displayDialog.value?.open(newToken.token);
	} catch {
		toast.error(
			i18n.t('components.accessControl.daemonAccessTokens.messages.rotate.failed', { id: token }),
		);
	}
	componentState.value = ComponentState.Idle;
}

function open(id: number): void {
	token = id;
	show.value = true;
}

function close(): void {
	show.value = false;
	token = null;
}

defineExpose({
	open,
});
</script>
